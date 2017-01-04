<?php

namespace QualityAssurance\Component\Console\Command;

use GitWrapper\GitCommand;
use GitWrapper\GitException;
use GitWrapper\GitWrapper;
use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use QualityAssurance\Component\Console\Helper\DrupalInfoFormatHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;


class DiffMakeFilesCommand extends Command
{
  protected function configure()
  {
    $this
      ->setName('diff:make')
      ->setDescription('Check make file for changes.')
      ->addOption('filename', null, InputOption::VALUE_OPTIONAL, 'The filename to check.')
      ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Path to recursively check.')
      ->addOption('exclude-dirs', null, InputOption::VALUE_OPTIONAL, 'Directories to exclude.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Find todos tags.
    $dirname = !empty($input->getOption('directory')) ? $input->getOption('directory') : getcwd();
    $filename = !empty($input->getOption('filename')) ? $input->getOption('filename') : '';
    $exclude_dirs = !empty($input->getOption('exclude-dirs')) ? explode(',', $input->getOption('exclude-dirs')) : NULL;

    if (!empty($filename) && pathinfo($filename, PATHINFO_EXTENSION) !== 'make') {
      return;
    }

    // Find site.make in resources folder.
    $searches = array(
      'projects' => 'modules or themes',
      'libraries' => 'libraries',
    );
    $makefile = $filename;
    // Get a diff of current branch and master.
    $wrapper = new GitWrapper();
    $git = $wrapper->workingCopy($dirname);
    $branches = $git->getBranches();
    $head = $branches->head();
    $diff = $git->diff('master', $makefile, $makefile);
    $filtered_diff = str_replace('"', '', $diff->getOutput());
    $master = DrupalInfoFormatHelper::drupalParseInfoFormat($git->show('master:' . str_replace(getcwd(). '/', '', $makefile)));
    $current = DrupalInfoFormatHelper::drupalParseInfoFormat(file_get_contents($makefile));

    // Find new projects or libraries.
    foreach ($searches as $search => $subject) {
      $current_items = isset($current[$search]) ? $current[$search] : array();
      $master_items = isset($master[$search]) ? $master[$search] : array();
      $added_items = array_diff_key($current_items, $master_items);
      $removed_items = array_diff_key($master_items, $current_items);
      $complete_items = array_merge($added_items, $removed_items);
      $untouched_items = array_keys(array_diff_key($current_items, $complete_items));

      // Check for new projects or libraries.
      if (!empty($added_items)) {
        $added_string = DrupalInfoFormatHelper::transformArrayIntoInfoFormat(array($search => $added_items));
        $output->writeln('Added ' . $subject . ' found: ' . count($added_items) . " found.");
        $output->writeln(preg_replace('/^/m', '+', $added_string));
      }
      // Check for removed projects or libraries.
      if (!empty($removed_items)) {
        $removed_string = DrupalInfoFormatHelper::transformArrayIntoInfoFormat(array($search => $removed_items));
        $output->writeln('Removed ' . $subject . ' found: ' . count($removed_items) . " found.");
        $output->writeln(preg_replace('/^/m', '-', $removed_string));
      }

      // Check for altered projects or libraries.
      $regex = '~^[\+|\-]' . $search . '\[(' . implode(')\].*?$|^[\+|\-]' . $search . '\[(', $untouched_items) . ')\].*?$~m';
      if (!is_null(preg_match_all($regex, $filtered_diff, $matches)) && !empty($matches[0])) {
        // Filter out empty arrays.
        $changed = array_map('array_filter', $matches);
        $changed = array_values(array_filter(array_values($changed)));
        $output->writeln('Altered ' . $subject . ' found: ' . count($changed[1]) . " found.");
        foreach ($changed as $key => $changed_array) {
          $changed_array = array_values($changed_array);
          if (!empty($changed_array) && $key != 0) {
            $new_item = array(' ' .$search => array($changed_array[0] => $current_items[$changed_array[0]]));
            $old_item = array(' ' .$search => array($changed_array[0] => $master_items[$changed_array[0]]));
            $new_string = DrupalInfoFormatHelper::transformArrayIntoInfoFormat($new_item);
            $old_string = DrupalInfoFormatHelper::transformArrayIntoInfoFormat($old_item);
            $combined_string = implode("\n", array_unique(explode("\n", $old_string . $new_string )));
            foreach ($changed[0] as $replacement) {
              $combined_string = str_replace(' ' . substr($replacement, 1), $replacement, $combined_string);
            }
            $output->writeln($combined_string);
          }
        }
      }
    }
  }
}