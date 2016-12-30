<?php

namespace QualityAssurance\Component\Console\Command;

use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class FullReviewCommand extends Command
{
  protected function configure()
  {
    $this
      ->setName('review:full')
      ->setDescription('Performs all QA checks on the codebase.')
      ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'QA review type: platform or subsite.', 'subsite')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Start off by fetching the review type.
    $type = $input->getOption('type');
    // Get the needed properties to target the QA review on.
    $properties = $this->getPhingProperties($type, $input, $output);
    // Fetch all modules, features and themes into an array.
    $info_files = $this->getInfoFiles($properties['lib']);
    // Fetch all make files into an array.
    $make_files = $this->getMakeFiles($properties['resources']);
    // Merge makes and infos into options.
    $options = array_merge($make_files, $info_files);
    // Add the "Select all" option.
    array_unshift($options, 'Select all');
    // Calculate the ruler length for header output.
    $ruler_length = $this->getRulerLength($options);

    // Stop for user input to select modules.
    $helperquestion = $this->getHelper('question');
    $question = new ChoiceQuestion("Select features, modules and/or themes to QA (seperate with commas): ", array_values($options), 0);
    $question->setMultiselect(true);
    $selection = $helperquestion->ask($input, $output, $question);

    // If user selects all, add all but that option to the selected options.
    if ($selection[0] == 'Select all') {
      array_shift($options);
      $selected = $options;
    }
    // If a targeted selection is made, just add those to the selected options.
    else {
      $selected = array_intersect($options, $selection);
    }

    foreach ($selected as $absolute_path => $filename) {
      $pathinfo = pathinfo($absolute_path);
      $directory = $pathinfo['dirname'];
      $filename = $pathinfo['filename'];
      $extension = $pathinfo['extension'];
      $shared_options = array(
        '--directory' => $directory,
      );
      $buffered_output = new BufferedOutput(
        $output->getVerbosity(),
        true // true for decorated
      );
      if ($extension == 'info') {
        if ($exclude_directories = $this->getSubmoduleDirectories($filename, $directory)) {
          $shared_options['--exclude-dirs'] = implode(',', $exclude_directories);
        }
        $commands = array(
          'scan:csi',
          'scan:tds',
          'scan:crn',
          'phpcs:xml',
          'diff:updb',
        );
        $commandlines = array();
        foreach ($commands as $command) {
          $commandlines[$command] = array_merge(array('command' => $command), $shared_options);

          switch ($command) {
            case 'scan:crn':
              $commandlines[$command] += array(
                '--filename' => $filename,
              );
              break;
            case 'phpcs:xml':
              $commandlines[$command] += array(
                '--standard' => $properties['phpcs-config'],
                '--width' => $ruler_length,
              );
              break;
            case 'diff:updb':
              $commandlines[$command] += array(
                '--filename' => $filename,
              );
              break;
          }
        }
      }
      if ($extension == 'make') {
        $commands = array(
          'diff:make',
          'scan:mkpd',
        );
        $commandlines = array();
        foreach ($commands as $command) {
          $commandlines[$command] = array_merge(array('command' => $command), $shared_options);

          switch ($command) {
            case 'diff:make':
              $commandlines[$command] += array(
                '--filename' => $absolute_path,
              );
              break;
            case 'scan:mkpd':
              $commandlines[$command] += array(
                '--filename' => $absolute_path,
                '--profile' => $properties['profile'],
              );
          }
        }
      }
      foreach ($commandlines as $commandline) {
        $command_input = new ArrayInput($commandline);
        $command = $this->getApplication()->find($commandline['command']);
        $returnCode = $command->run($command_input, $buffered_output);
      }

      if ($content = $buffered_output->fetch()) {
        $count = count($exclude_directories);
        $multiple = $count > 1 ? 's' : '';
        $exclusion = !empty($exclude_directories) ? ":<info> $count submodule$multiple excluded.</info>" : "";
        $output->writeln("");
        $output->writeln("<info>" . str_repeat('=', $ruler_length) . "</info>");
        $output->writeln(str_replace(getcwd(), '.', $directory) . $exclusion);
        $output->writeln("<info>" . str_repeat('=', $ruler_length) . "</info>");
        $output->writeln($content);
      }
//
//    // If an error was discovered, fail the build.
//    if (!$this->passbuild) {
//      throw new \BuildException(
//        'Build failed because the code did not pass quality assurance checks.'
//      );
    }

  }

  /**
   * Helper function to gather submodule directories to exclude.
   *
   * @param string $filename
   *   The main module filename we don't want to match.
   * @param string $directory
   *   The directory of the module to search through.
   * @return array
   *   An array of submodule directory names.
   */
  private function getSubmoduleDirectories($filename, $directory) {
    $submodule_directories = array();
    $submodules = new Finder();
    $submodules->files()
      ->name('*.info')
      ->notName($filename . '.info')
      ->in($directory)
      ->sortByName();
    foreach ($submodules as $submodule) {
      $submodule_directories[] = basename($submodule->getRelativePath());
    }
    return $submodule_directories;
  }

  /**
   * Helper function to return the needed build properties to target the QA on.
   *
   * @param string $type
   *   The type of QA review: subsite or platform.
   * @param InputInterface $input
   *   The input of this command.
   * @param OutputInterface $output
   *   The output of this command.
   * @return array
   *   The required properties.
   * @throws \Symfony\Component\Debug\Exception\FatalErrorException
   */
  private function getPhingProperties($type, $input, $output) {
    // Get required properties from the build properties depending on QA review type.
    $phingPropertiesHelper = new PhingPropertiesHelper($input, $output);
    $properties = array();
    if ($input->getOption('type') == 'subsite') {
      $properties = $phingPropertiesHelper->requestSettings(array(
        'lib' => 'subsite.resources.lib.dir',
        'resources' => 'subsite.resources.dir',
        'phpcs-config' => 'phpcs.config',
        'profile' => 'platform.profile.name',
      ));
    }
    else {
      $properties = $phingPropertiesHelper->requestSettings(array(
        'lib' => 'platform.resources.profiles.dir',
        'resources' => 'platform.resources.dir',
        'phpcs-config' => 'phpcs.config',
      ));
    }
    return $properties;
  }

  /**
   * Helper function to get info file select options.
   *
   * @param $path
   *   The path in which to look for the info files.
   * @return array
   *   An associative array of filenames keyed with absolute filepath.
   */
  private function getInfoFiles($path) {
    $options = array();
    // Find all info files in provided path.
    $finder = new Finder();
    $finder->files()
      ->name('*.info')
      ->in($path)
      ->exclude(array('contrib', 'contributed'))
      ->sortByName();
    // Loop over files and build an options array.
    foreach ($finder as $file) {
      $filepathname = $file->getRealPath();
      $filename = basename($filepathname);
      $options[$filepathname] = $filename;
    }
    return $options;
  }

  /**
   * Helper function to get make file select options.
   *
   * @param $path
   *   The path in which to look for the make files.
   * @return array
   *   An associative array of filenames keyed with absolute filepath.
   */
  private function getMakeFiles($path) {
    $options = array();
    // Find all info files in provided path.
    $finder = new Finder();
    $finder->files()
      ->name('*.make')
      ->in($path)
      ->sortByName();
    // Loop over files and build an options array.
    foreach ($finder as $file) {
      $filepathname = $file->getRealPath();
      $filename = basename($filepathname);
      $options[$filepathname] = $filename;
    }
    return $options;
  }

  /**
   * Helper function to the ruler length for the messages.
   *
   * @param array $options
   *   Options array to check for longest value.
   * @return int
   *   An integer that represents the ruler length.
   */
  private function getRulerLength($options) {

    $ruler_length = 80;
    foreach ($options as $path => $filename) {
      $dirname = dirname($path);
      $relative_dirname = str_replace(getcwd(), '.', $dirname);
      $ruler_length = strlen($relative_dirname) > $ruler_length ? strlen($relative_dirname) : $ruler_length;
    }
    return $ruler_length;
  }
}