<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\ReviewSelectCommand.
 */

namespace QualityAssurance\Component\Console\Command;

use QualityAssurance\Component\Console\Helper\ReviewCommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class ReviewSelectCommand
 * @package QualityAssurance\Component\Console\Command
 */
class ReviewSelectCommand extends Command
{
  /**
   * Command configuration.
   */
  protected function configure()
  {
    $this
      ->setName('review:select')
      ->setDescription('Performs a selection of QA checks on the codebase.')
      ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'QA review type: platform or subsite.', 'subsite')
    ;
  }

  /**
   * Command execution.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Get the application
    $application = $this->getApplication();
    // Get all application commands.
    $commands = $application->all();

    // Unset unwanted commands.
    foreach($commands as $name => $command) {
      if (in_array($name, array('help', 'list')) || strpos($name, 'review:') === 0) {
        unset($commands[$name]);
      }
    }

    // Stop for user input to select commands.
    $helperquestion = new QuestionHelper();
    $question = new ChoiceQuestion("Select commands to execute in review (seperate with commas): ", array_keys($commands), 0);
    $question->setMultiselect(true);
    $selection = $helperquestion->ask($input, $output, $question);

    // Set selected commands.
    if ($selection) {
      $selected = array_intersect_key($commands, array_flip($selection));
    }

    // Setup the reviewCommandHelper.
    $reviewCommandHelper = new ReviewCommandHelper($input, $output, $selected);
    // Ask for a selection of options if needed.
    $selected = $reviewCommandHelper->getSelectedOptions();
    // Setup a buffered output to capture results of command.
    $buffered_output = new BufferedOutput($output->getVerbosity(), true);

    // Loop over each selection to run commands.
    foreach ($selected as $absolute_path => $filename) {
      // Build the commandlines.
      $commandlines = $reviewCommandHelper->buildCommandlines($absolute_path);
      // Execute commandlines.
      $reviewCommandHelper->executeCommandlines($application, $commandlines, $buffered_output);
      // Write the results.
      $reviewCommandHelper->outputCommandlines($buffered_output, dirname($absolute_path));
      // @todo: incorporate build exception.
//    // If an error was discovered, fail the build.
//    if (!$this->passbuild) {
//      throw new \BuildException(
//        'Build failed because the code did not pass quality assurance checks.'
//      );
    }
  }
}