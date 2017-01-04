<?php

namespace QualityAssurance\Component\Console\Command;

use QualityAssurance\Component\Console\Helper\ReviewCommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReviewFullCommand extends Command
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
    // Get the application
    $application = $this->getApplication();

    // Get all application commands.
    $commands = $application->all();

    // Setup the reviewCommandHelper.
    $reviewCommandHelper = new ReviewCommandHelper($input, $output, $commands);

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