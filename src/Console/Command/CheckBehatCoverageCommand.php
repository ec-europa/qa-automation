<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\CheckBehatCoverageCommand.
 */
namespace QualityAssurance\Component\Console\Command;

use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckBehatCoverageCommand
 * @package QualityAssurance\Component\Console\Command
 */
class CheckBehatCoverageCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('check:behat')
            ->setDescription('Check the behat coverage.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Inform user he or she is up to date.
        $output->writeln('<info>The starterkit is up to date.</info>');
    }
}
