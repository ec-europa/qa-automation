<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\CheckCodingStandardsCommand.
 */

namespace QualityAssurance\Component\Console\Command;

use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CheckCodingStandardsCommand
 * @package QualityAssurance\Component\Console\Command
 */
class CheckCodingStandardsCommand extends Command
{
    protected function configure()
    {
        $phingPropertiesHelper = new PhingPropertiesHelper(new NullOutput());
        $properties = $phingPropertiesHelper->getAllSettings();

        // @codingStandardsIgnoreStart
        $this
            ->setName('phpcs:run')
            ->setDescription('Perform a phpcs run with provided phpcs.xml standard.')
            ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Path to run PHPCS on.')
            ->addOption('standard', null, InputOption::VALUE_OPTIONAL, 'PHPCS standard.', $properties['phpcs.config'])
            ->addOption('exclude-dirs', null, InputOption::VALUE_OPTIONAL, 'Directories to exclude.')
            ->addOption('width', null, InputOption::VALUE_OPTIONAL, 'Width of the report.')
            ->addOption('show', null, InputOption::VALUE_NONE, 'If option is given description is shown.')
            ->addOption('toolkit.dir.bin', null, InputOption::VALUE_REQUIRED, 'The binary to phpcs.', $properties['toolkit.dir.bin'])
            ->addOption('project.basedir', null, InputOption::VALUE_REQUIRED, 'The project basedir to find phpcs.', $properties['project.basedir']);
        // @codingStandardsIgnoreEnd
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $dirname = !empty($input->getOption('directory')) ? $input->getOption('directory') : '';
        // @codingStandardsIgnoreLine
        $exclude_dirs = !empty($input->getOption('exclude-dirs')) ? '--ignore=' . $input->getOption('exclude-dirs') . ' ' : '';
        $standard = !empty($input->getOption('standard')) ? $input->getOption('standard') : '';
        $basedir = $input->getOption('project.basedir');
        $executable = $input->getOption('toolkit.dir.bin') . '/phpcs';

        //$width = !empty($input->getOption('width')) ? $input->getOption('width') : 80;
        $show = $input->getOption('show') ? true : false;
        ob_start();
        passthru($executable . " --standard=$standard $exclude_dirs --report=emacs -qvs " . $dirname, $error);
        $phpcs = ob_get_contents();
        ob_end_clean();
        if ($error && preg_match_all('/^\/(.*)$/m', $phpcs, $emacs)) {
            $count = count($emacs[0]);
            $output->writeln("<comment>Coding standards violations: </comment><info>$count detected.</info>");
            foreach ($emacs[0] as $emac) {
                $vars = preg_split('/[:\(\)]/', $emac, 0, PREG_SPLIT_NO_EMPTY);
                if (is_array($vars)) {
                    $path = str_replace($dirname, '.', array_shift($vars));
                    $line = array_shift($vars);
                    $char = array_shift($vars);
                    $sniff = array_pop($vars);
                    $type_message = explode(' - ', trim(implode('()', $vars)), 2);
                    $type = $type_message[0];
                    $message = rtrim($type_message[1], '.') . '.';
                    $output->writeln("$path:$line:$type:$sniff");
                    if ($output->isVerbose()) {
                        $output->writeln("  <comment>$message</comment>");
                    }
                }
            }
            // FAIL.
            return 1;
        }
    }
}
