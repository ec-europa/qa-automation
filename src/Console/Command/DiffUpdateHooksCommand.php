<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\DiffUpdateHooksCommand.
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
use Symfony\Component\Finder\Finder;

/**
 * Class DiffUpdateHooksCommand
 * @package QualityAssurance\Component\Console\Command
 */
class DiffUpdateHooksCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('diff:updb')
            ->setDescription('Check diff for new update hooks.')
            ->addOption('filename', null, InputOption::VALUE_OPTIONAL, 'The filename to check.')
            ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Path to recursively check.')
            ->addOption('exclude-dirs', null, InputOption::VALUE_OPTIONAL, 'Directories to exclude.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Find todos tags.
        $dirname = !empty($input->getOption('directory')) ? $input->getOption('directory') : getcwd();
        $filename = !empty($input->getOption('filename')) ? $input->getOption('filename') . '.install' : '*.install';
        // @codingStandardsIgnoreLine
        $exclude_dirs = !empty($input->getOption('exclude-dirs')) ? explode(',', $input->getOption('exclude-dirs')) : null;
        $schema_versions = array();
        // Find install files in the requested folder.
        $finder = new Finder();
        $finder->files()
            ->name($filename)
            ->in($dirname)
            ->exclude((array) $exclude_dirs);
        // Perform the search in the diff for each file.
        foreach ($finder as $file) {
            // Get a diff of current branch and master.
            $filepath = $file->getRealPath();
            $filename = basename($filepath);
            $wrapper = new GitWrapper();
            $git = $wrapper->workingCopy($dirname);
            $branches = $git->getBranches();
            $head = $branches->head();
            $diff = $git->diff('master', $head, $filepath);

            // Find hook update functions in diff.
            $regex = '~(\+|\-)' . 'function ' . $filename . '_update_' . '(7\d{3})~';
            $contents = is_file($filepath) ? file_get_contents($filepath) : '';
            preg_match_all($regex, $diff, $results);
            $function_names = array_unique(str_replace(array('+', '-'), '', $results[0]));
            $diff_actions = isset($results[1]) ? $results[1] : array();
            $schema_versions = isset($results[2]) ? $results[2] : array();
        }

        if ($schema_versions) {
            $count = count(array_unique($schema_versions));
            $plural = $count > 1 ? 's' : '';
            $output->writeln("Check for new updates in branch: $count update$plural found.");
            $this->printAllFoundFunctionNames($filename, $function_names, $contents, $output);
        }
    }

    /**
     * Print all found function names.
     *
     * @param string $filename
     *   The name of the file.
     * @param array $function_names.
     *   An array with the function names.
     * @param string $contents
     *   The contents of the file.
     */
    private function printAllFoundFunctionNames($filename, $function_names, $contents, $output)
    {
        // Print the found hooks with file and line number.
        // @codingStandardsIgnoreLine
        preg_match_all('~' . implode('\((.*)(?=\))\)|', $function_names) . '\((.*)(?=\))\)~', $contents, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $key => $match) {
            list($before) = str_split($contents, $match[1]);
            $line_number = strlen($before) - strlen(str_replace("\n", "", $before)) + 1;
            $output->writeln($filename . '.install:' . $line_number . ':' . $match[0]);
        }
    }
}
