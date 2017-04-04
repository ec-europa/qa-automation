<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Helper\ReviewCommandThemeHelper.
 */

namespace QualityAssurance\Component\Console\Helper;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class ReviewCommandThemeHelper
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
    public function __construct(InputInterface $input, OutputInterface $output, $application)
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
    public function startReview($section = false)
    {
        $failbuild = false;

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
                $failbuild = true;
            }
            // Write the results.
            $this->outputCommandlines($buffered_output, dirname($absolute_path));
        }

        if ($failbuild) {
            return 1;
        }
    }

    protected function getSelectedCommands($input, $output, $application)
    {
        // Get all application commands.
        $commands = $application->all();

        // Unset unwanted commands.
        foreach ($commands as $name => $command) {
            $filter = strpos($name, 'theme:');
            if ($filter !== 0) {
                unset($commands[$name]);
            }
        }

        // Stop for user input to select commands.
        if ($this->input->getOption('select')) {
            $question_str = "Select commands to execute in review (separate with commas): ";
            $helperquestion = new QuestionHelper();
            $question = new ChoiceQuestion($question_str, array_keys($commands), 0);
            $question->setMultiselect(true);
            $selection = $helperquestion->ask($input, $output, $question);

            // Set selected commands.
            if ($selection) {
                return array_intersect_key($commands, array_flip($selection));
            }
        } else {
            return $commands;
        }
    }
}
