<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\ReviewThemeCommand.
 */

namespace QualityAssurance\Component\Console\Command;

use QualityAssurance\Component\Console\Helper\ReviewCommandThemeHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReviewThemeCommand
 * @package QualityAssurance\Component\Console\Command
 */
class ReviewThemeCommand extends Command
{
  /**
   * Command configuration.
   */
  protected function configure()
  {
    $this
      ->setName('review:theme')
      ->setDescription('Performs all required QA checks on the theme.')
      ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'QA review type: platform or subsite.', 'subsite')
      ->addOption('select', null, InputOption::VALUE_NONE, 'Allows you to set which commands to run.')
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
    // Setup the reviewCommandHelper.
    $reviewCommandHelper = new ReviewCommandThemeHelper($input, $output, $application);
    // Change the lib property to the current folder.
    $reviewCommandHelper->setProperties(array('lib' => getcwd()));
    // Start the review.
    $reviewCommandHelper->startReview('theme');
  }
}
