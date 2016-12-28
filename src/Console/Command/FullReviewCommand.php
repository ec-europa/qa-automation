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
      ->setDescription('Performs all QA checks on a distribution build.')
//      ->addOption('project.basedir', null, InputOption::VALUE_REQUIRED, 'Project base directory.')
//      ->addOption('subsite.make', null, InputOption::VALUE_REQUIRED, 'The subsite site make file.')
//      ->addOption('subsite.resources.dir', null, InputOption::VALUE_REQUIRED, 'The subsite resources directory.')
      ->addOption('dist.build.dir', null, InputOption::VALUE_OPTIONAL, 'The distribution build directory.');
//      ->addOption('subsite.resources.lib.dir', null, InputOption::VALUE_REQUIRED, 'The subsite resources library directory.')
//      ->addOption('platform.profile.name', null, InputOption::VALUE_REQUIRED, 'The platform profile name.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Get the needed options for if the call came from console and not from phing.
    $phingPropertiesHelper = new PhingPropertiesHelper($input, $output);
    $options = $phingPropertiesHelper->requestRequiredSettings($input->getOptions());

    // Prepare option variables for future usage.
//    $projectBasedir = !empty($input->getOption('project.basedir')) ? $input->getOption('project.basedir') : $options['project.basedir'];
//    $subsiteMake = !empty($input->getOption('subsite.make')) ? $input->getOption('subsite.make') : $options['subsite.make'];
//    $subsiteResourcesDir = !empty($input->getOption('subsite.resources.dir')) ? $input->getOption('subsite.resources.dir') : $options['subsite.resources.dir'];
    $distBuildDir = !empty($input->getOption('dist.build.dir')) ? $input->getOption('dist.build.dir') : $options['dist.build.dir'];
//    $subsiteResourcesLibDir = !empty($input->getOption('subsite.resources.lib.dir')) ? $input->getOption('subsite.resources.lib.dir') : $options['subsite.resources.lib.dir'];
//    $platformProfileName = !empty($input->getOption('platform.profile.name')) ? $input->getOption('platform.profile.name') : $options['platform.profile.name'];

    // Find all info files in our build folder.
    $finder = new Finder();
    $finder->files()
      ->name('*.info')
      ->in($distBuildDir)
      ->exclude(array('contrib', 'contributed'))
      ->sortByName();
    // Loop over files and extract the info files.
    $options = array('Select all');
    $ruler_length = 0;
    foreach ($finder as $file) {
      $filepathname = $file->getRealPath();
      $filename = basename($filepathname);
      $dirname = dirname($filepathname);
      $relative_dirname = str_replace(getcwd(), '.', $dirname);
      $ruler_length = strlen($relative_dirname) > $ruler_length ? strlen($relative_dirname) : $ruler_length;
      $options[$filepathname] = $filename;
    }
    // Stop for selection of module if autoselect is disabled.
    if (TRUE) {
      $helperquestion = $this->getHelper('question');
      $question = new ChoiceQuestion("Select features, modules and/or themes to QA (seperate with commas): ", array_values($options), 0);
      $question->setValidator(function ($selection, $options) {
        var_dump($selection);
        if (!is_array($selection)) {
          throw new \RuntimeException(
            'You have an invalid selection, please try again.'
          );
        }
      });
      $question->setMultiselect(true);
//      $question->setErrorMessage('Values %s are invalid.');

      $selection = $helperquestion->ask($input, $output, $question);
      if ($selection[0] == 'Select all') {
        array_shift($options);
        $selected = $options;
      }
      else {
        $selected = array_intersect($options, $selection);
      }

      foreach ($selected as $absolute_path => $filename) {
        $pathinfo = pathinfo($absolute_path);
        $directory = $pathinfo['dirname'];
        $filename = $pathinfo['filename'];
        $shared_options = array(
          '--directory' => $directory,
        );
        $exclude_directories = array();
        $submodules = new Finder();
        $submodules->files()
          ->name('*.info')
          ->notName($filename . '.info')
          ->in($directory)
          ->sortByName();
        foreach ($submodules as $submodule) {
          $exclude_directories[] = basename($submodule->getRelativePath());
        }
        if (!empty($exclude_directories)) {
          $shared_options['--exclude-dirs'] = implode(',', $exclude_directories);
        }
        $buffered_output = new BufferedOutput(
          $output->getVerbosity(),
          true // true for decorated
        );
        $commands = array('scan:csi', 'scan:tds', 'scan:crn', 'phpcs:xml');
        $commandlines = array();
        foreach ($commands as $command) {
          $commandlines[$command] = array_merge(array('command' => $command), $shared_options);

          switch ($command) {
            case 'scan:crn':
              $commandlines[$command] += array(
                '--filename' => $filename
              );
              break;
            case 'phpcs:xml':
              $commandlines[$command] += array(
                '--standard' => 'phpcs.xml',
                '--width' => $ruler_length
              );
              break;
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
      }
//      $output->writeln('You have just selected: ' . implode(', ', $selection));
//    // Start the QA on selected features, modules and/or themes.
//    $this->startQa($selected);
//
//    // If an error was discovered, fail the build.
//    if (!$this->passbuild) {
//      throw new \BuildException(
//        'Build failed because the code did not pass quality assurance checks.'
//      );
    }

  }
}