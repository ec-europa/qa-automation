<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\ReviewFullCommand.
 */

namespace QualityAssurance\Component\Console\Command;

use QualityAssurance\Component\Console\Helper\ReviewCommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReviewFullCommand
 * @package QualityAssurance\Component\Console\Command
 */
class ReviewFullCommand extends Command
{
    /**
     * Command configuration.
     */
    protected function configure()
    {
        $this
            ->setName('review:full')
            ->setDescription('Performs all required QA checks on the entire codebase.')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'QA review type: platform or subsite.', 'subsite')
            ->addOption('select', null, InputOption::VALUE_NONE, 'Allows you to set which commands to run.');
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
        $reviewCommandHelper = new ReviewCommandHelper($input, $output, $application);
        // Set properties.
        $reviewCommandHelper->setProperties();
        // Start the review.
        if ($reviewCommandHelper->startReview()) {
            $output->writeln("<error>Code did not pass quality assurance checks.</error>");
            return 1;
        }
    }
}
