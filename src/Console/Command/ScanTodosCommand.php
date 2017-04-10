<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\ScanTodosCommand.
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
 * Class ScanTodosCommand
 * @package QualityAssurance\Component\Console\Command
 */
class ScanTodosCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('scan:todo')
            ->setDescription('Scan for pending refractoring tasks.')
            ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Path to recursively check.')
            ->addOption('exclude-dirs', null, InputOption::VALUE_OPTIONAL, 'Directories to exclude.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Find todos tags.
        $dirname = !empty($input->getOption('directory')) ? $input->getOption('directory') : getcwd();
        // @codingStandardsIgnoreLine
        $exclude_dirs = !empty($input->getOption('exclude-dirs')) ? explode(',', $input->getOption('exclude-dirs')) : null;
        // @codingStandardsIgnoreLine
        $exclude_dir = is_array($exclude_dirs) ? '--exclude-dir=' . implode(' --exclude-dir=', $exclude_dirs) . ' ' : '';
        $search_for = array(
          '@todo: .*?MULTISITE-[0-9]{5}.*?',
        );
        $search_pattern = implode('|', $search_for);
        if (exec("grep -IPrino $exclude_dir'{$search_pattern}' {$dirname}", $results)) {
            $plural = count($results) > 1 ? '\'s' : '';
            // @codingStandardsIgnoreLine
            $output->writeln("<comment>Scan for pending tasks: </comment><info>" . count($results) . " todo" . $plural . " found.</info>");
            foreach ($results as $result) {
                $lines = explode(':', str_replace($dirname, '.', $result));
                $output->writeln(implode(':', array_map('trim', $lines)));
            }
        }
    }
}
