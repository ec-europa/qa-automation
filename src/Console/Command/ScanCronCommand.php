<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\ScanCronCommand.
 */

namespace QualityAssurance\Component\Console\Command;

use GitWrapper\GitCommand;
use GitWrapper\GitException;
use GitWrapper\GitWrapper;
use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ScanCronCommand
 * @package QualityAssurance\Component\Console\Command
 */
class ScanCronCommand extends Command
{
    protected function configure()
    {
        $this
          ->setName('scan:cron')
          ->setDescription('Scan for cron implementations.')
          ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Path to recursively check.')
          ->addOption('exclude-dirs', null, InputOption::VALUE_OPTIONAL, 'Directories to exclude.')
          ->addOption('filename', null, InputOption::VALUE_OPTIONAL, 'Modulename.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Find todos tags.
        $dirname = !empty($input->getOption('directory')) ? $input->getOption('directory') : getcwd();
        // @codingStandardsIgnoreStart
        $exclude_dirs = !empty($input->getOption('exclude-dirs')) ? explode(',', $input->getOption('exclude-dirs')) : null;
        $exclude_dir = is_array($exclude_dirs) ? '--exclude-dir=' . implode(' --exclude-dir=', $exclude_dirs) . ' ' : '';
        // @codingStandardsIgnoreEnd
        $filename = !empty($input->getOption('filename')) ? $input->getOption('filename') : '@todo';

        $search_pattern = $filename . '_cron';
        if (exec("grep -IPrino  $exclude_dir'{$search_pattern}' {$dirname}", $results)) {
            $output->writeln("<comment>Cron implementation: </comment><info>hook found.</info>");
            foreach ($results as $result) {
                $output->writeln(str_replace($dirname, '.', $result));
            }
        }
    }
}
