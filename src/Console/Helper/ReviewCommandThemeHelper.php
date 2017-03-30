<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Helper\ReviewCommandThemeHelper.
 */

namespace QualityAssurance\Component\Console\Helper;


use QualityAssurance\Component\Console\Helper\ReviewCommandHelper;
use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Finder\Finder;

/**
 * Class ReviewCommandHelper
 * @package QualityAssurance\Component\Console\Helper
 */
class ReviewCommandThemeHelper extends ReviewCommandHelper
{
  /**
   * ReviewCommandHelper constructor.
   *
   * Setup our input output interfaces and other variables.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @param $application
   */
  function __construct(InputInterface $input, OutputInterface $output, $application)
  {
    // Set construct properties.
    $this->input = $input;
    $this->output = $output;
    $this->application = $application;
    $this->commands = $this->getSelectedCommands($input, $output, $application);

  }

  /**
   * Start a review process.
   */
  public function startReview($section = FALSE) {
    $failbuild = FALSE;

    // Perform the starterkit check if needed.
    if (!empty($this->properties['check-ssk'])) {
      if ($this->executeCommandlines($this->application, array('check:ssk' => new ArrayInput(array())), $this->output)) {
        return 1;
      }
    }
    
    // Ask for a selection of options if needed.
    $selected = $this->getSelectedOptions($section);
    // Setup a buffered output to capture results of command.
    $buffered_output = new BufferedOutput($this->output->getVerbosity(), true);

    // Loop over each selection to run commands.
    foreach ($selected as $absolute_path => $filename) {
      // Build the commandlines.
      $commandlines = $this->buildCommandlines($absolute_path);
      // Execute commandlines.
      if ($this->executeCommandlines($this->application, $commandlines, $buffered_output)) {
        $failbuild = TRUE;
      }
      // Write the results.
      $this->outputCommandlines($buffered_output, dirname($absolute_path));
    }

    if ($failbuild) {
      return 1;
    }
  }

}
