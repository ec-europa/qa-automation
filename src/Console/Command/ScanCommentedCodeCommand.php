<?php

/**
 * @file
 * File for command class to run PHPCS on specified directory or current directory.
 *
 * Contains QualityAssurance\Component\Console\Command\CheckCodingStandardsCommand.
 */

namespace QualityAssurance\Component\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * .
 */
class ScanCommentedCodeCommand extends Command
{
  protected function configure()
  {
    $this
      ->setName('scan:coco')
      ->setDescription('Scan for possible commented code.')
      ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Path to run PHPCS on.')
      ->addOption('exclude-dirs', null, InputOption::VALUE_OPTIONAL, 'Directories to exclude.')
      ->addOption('width', null, InputOption::VALUE_OPTIONAL, 'Width of the report.')
      ->addOption('show', null, InputOption::VALUE_NONE, 'If option is given description is shown.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $dirname = !empty($input->getOption('directory')) ? $input->getOption('directory') : getcwd();
    $exclude_dirs = !empty($input->getOption('exclude-dirs')) ? '--ignore=' . $input->getOption('exclude-dirs') . ' ' : '';
    //$width = !empty($input->getOption('width')) ? $input->getOption('width') : 80;
    $show = $input->getOption('show') ? TRUE : FALSE;
    ob_start();
    //passthru("./bin/phpcs --standard=$standard --report-width=$width -qv " . $dirname, $error);
    passthru("./bin/phpcs --standard=" . __DIR__ . "/../../../resources/custom-rulesets/coco-50.xml $exclude_dirs --report=emacs -qvs " . $dirname, $error);
    $phpcs = ob_get_contents();
    ob_end_clean();
    if($error && preg_match_all('/^\/(.*)$/m', $phpcs, $emacs)) {
      $count = count($emacs[0]);
      $output->writeln("<comment>Possible commented code: </comment><info>$count detected.</info>");
      foreach($emacs[0] as $emac) {
        $vars = preg_split('/[:\(\)]/', $emac, 0, PREG_SPLIT_NO_EMPTY);if (is_array($vars)) {
          $path = str_replace($dirname, '.', array_shift($vars));
          $line = array_shift($vars);
          $char = array_shift($vars);
          $sniff = array_pop($vars);
          $type_message = explode(' - ', trim(implode('()', $vars)), 2);
          $type = $type_message[0];
          $message = rtrim($type_message[1], '.') . '.';
          $output->writeln("$path:$line:$type:$sniff");
          if (TRUE) {
//          if ($show && $char) {
            $output->writeln("  <comment>$message</comment>");
          }
        }
      }
    }
  }
}