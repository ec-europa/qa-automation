<?php

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

class CheckBypassCodingStandardsCommand extends Command
{
  protected function configure()
  {
    $this
      ->setName('scan:csi')
      ->setDescription('Scan for codingStandardsIgnore tags.')
      ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Path to recursively check.')
      ->addOption('exclude-dirs', null, InputOption::VALUE_OPTIONAL, 'Directories to exclude.')
      ->addOption('show', null, InputOption::VALUE_NONE, 'If option is given, code is shown.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Find codingStandardsIgnore tags.
    $dirname = !empty($input->getOption('directory')) ? $input->getOption('directory') : getcwd();
    $exclude_dirs = !empty($input->getOption('exclude-dirs')) ? explode(',', $input->getOption('exclude-dirs')) : NULL;
    $exclude_dir = is_array($exclude_dirs) ? '--exclude-dir=./' . implode(' --exclude-dir=', $exclude_dirs) . ' ' : '';
    $show = $input->getOption('show') ? TRUE : FALSE;
    $messages = array();
    $search_for = array(
      '@codingStandardsIgnoreStart',
      '@codingStandardsIgnoreFile',
      '@codingStandardsIgnoreLine',
    );
    $search_pattern = implode('|', $search_for);
    if (exec("grep -IPrino $exclude_dir'{$search_pattern}' {$dirname}", $results)) {
      if ($results) {
        $messages[] = "<comment>Coding standards ignores: </comment><info>" . count($results) . " detected.</info>";
        foreach ($results as $result) {
          $params = explode(':', $result);
          $start = $params[1] + 1;
          $file = $params[0];
          $type = $params[2];

          $messages[] = str_replace($dirname, '', $result);
//          if (strpos($type, '@codingStandardsIgnoreFile') !== FALSE) {
//            $messages[] = "<comment>" . $params[0] . "</comment>";
//          }
          if ($show) {
            if (strpos($type, '@codingStandardsIgnoreLine') !== FALSE) {
              $messages[] = "<comment>  " . ltrim(exec("sed \"$start q;d\" $file")) . "</comment>";
            } elseif (strpos($type, '@codingStandardsIgnoreStart') !== FALSE) {
              $search_pattern = '(?s)(.*?)@codingStandardsIgnoreEnd';
              if (exec("tail -n +{$start} {$file} | grep -Pzo '{$search_pattern}'", $code)) {
                $code[0] = "<comment>" . $code[0];
                $code[count($code) - 1] = $code[count($code) - 1] . "</comment>";
                $messages = array_merge($messages, $code);
                unset($code);
              }
            } else {

            }
          }
        }
        $output->writeln($messages);
      }
    }
  }
}