<?php
namespace QualityAssurance\Component\Console\Helper;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\ArrayInput;
use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;


class ReviewCommandHelper
{
  /**
   * PhingPropertiesHelper constructor.
   *
   * Setup our input output interfaces and other variables.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @param array $commands
   */
  function __construct(InputInterface $input, OutputInterface $output, $commands)
  {
    $this->input = $input;
    $this->output = $output;
    $this->commands = $commands;

    list($properties, $options, $ruler_length) = $this->buildVariableArray($input, $output);

    $this->properties = $properties;
    $this->options = $options;
    $this->ruler_length = $ruler_length;
  }

  /**
   * Public helper function to ask users to select options if needed.
   *
   * @return array
   *   An associative array of options keyed by absolute path and valued by filename.
   */
  public function getSelectedOptions() {

    // Stop for user input to select modules.
    $helperquestion = new QuestionHelper();
    $question = new ChoiceQuestion("Select features, modules and/or themes to QA (seperate with commas): ", array_values($this->options), 0);
    $question->setMultiselect(true);
    $selection = $helperquestion->ask($this->input, $this->output, $question);

    // If user selects all, add all but that option to the selected options.
    if ($selection[0] == 'Select all' || $this->input->getOption('no-interaction')) {
      array_shift($this->options);
      $selected = $this->options;
    }
    // If a targeted selection is made, just add those to the selected options.
    else {
      $selected = array_intersect($this->options, $selection);
    }

    return $selected;
  }

  /**
   * Public helper function to build the commandlines.
   *
   * @param $absolute_path
   *   The absolute path of the info file or make file.
   * @return array
   *   An associative array containing ArrayInput instances keyed by command name.
   */
  public function buildCommandlines($absolute_path) {
    $pathinfo = pathinfo($absolute_path);
    $directory = $pathinfo['dirname'];
    $filename = $pathinfo['filename'];
    $extension = $pathinfo['extension'];
    $command_options = array(
      'directory' => $directory,
      'filename' => $filename,
      'profile' => $this->properties['profile'],
      'standard' => $this->properties['phpcs-config'],
    );
    if ($exclude_directories = $this->getSubmoduleDirectories($filename, $directory)) {
      $command_options['exclude-dirs'] = implode(',', $exclude_directories);
    }

    $commandlines = array();
    $commands = $this->commands;
    // Unset the standard console commands.
    unset($commands['help']);
    unset($commands['list']);
    //unset($commands['scan:coco']);
    foreach ($commands as $command) {
      $command_name = $command->getName();
      $definition = $command->getDefinition();
      $arguments = $definition->getOptions();
      foreach ($command_options as $name => $shared_option) {
        if (isset($arguments[$name])) {
          $commandlines[$command_name]['--' . $name] = $command_options[$name];
        }
      }
      // Convert commandline to InputArray.
      if (isset($commandlines[$command_name])) {
        $commandlines[$command_name] = new ArrayInput((array) $commandlines[$command_name]);
      }
    }

    return $commandlines;
  }

  /**
   * Public helper function to execute the commmandlines array.
   *
   * @param $application
   *   The application of which we need to execute commands.
   * @param $commandlines
   *   An associative array of commandlines keyed by name and valued by ArrayInput.
   * @param $buffered_output
   *   The buffered output on which we capture the results.
   */
  public function executeCommandlines($application, $commandlines, $buffered_output) {
    foreach ($commandlines as $name => $commandline) {
      $command = $application->find($name);
      // @todo: implement the return code somehow.
      $returnCode = $command->run($commandline, $buffered_output);
    }
  }

  /**
   * Public helper function to output the commandlines results.
   *
   * @param $buffered_output
   *  The buffered output where the results were captured.
   * @param $title
   *  The path to the folder that was reviewed.
   */
  public function outputCommandlines($buffered_output, $title) {
    if ($content = $buffered_output->fetch()) {
      $ruler = "<info>" . str_repeat('=', $this->ruler_length) . "</info>";
      $this->output->writeln("");
      $this->output->writeln($ruler);
      $this->output->writeln(str_replace(getcwd(), '.', $title));
      $this->output->writeln($ruler);
      $this->output->writeln($content);
    }
  }

  /**
   * Build an array of variables needed for review commands.
   *
   * @return array $variables
   *   An array consisting of:
   *   - array of the build properties.
   *   - array of options.
   *   - integer of ruler length.
   */
  private function buildVariableArray() {
    // Start off by fetching the review type.
    $type = $this->input->getOption('type');
    // Get the needed properties to target the QA review on.
    $properties = $this->getPhingProperties($type, $this->input, $this->output);
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

    $variables = array(
      $properties,
      $options,
      $ruler_length,
    );

    return $variables;
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
   * @param InputInterface $this->input
   *   The input of this command.
   * @param OutputInterface $this->output
   *   The output of this command.
   * @return array
   *   The required properties.
   * @throws \Symfony\Component\Debug\Exception\FatalErrorException
   */
  private function getPhingProperties($type) {
    // Get required properties from the build properties depending on QA review type.
    $phingPropertiesHelper = new PhingPropertiesHelper($this->input, $this->output);
    $properties = array();
    if ($this->input->getOption('type') == 'subsite') {
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
