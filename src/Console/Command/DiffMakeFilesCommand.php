<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\DiffMakeFilesCommand.
 */
namespace QualityAssurance\Component\Console\Command;

use GitWrapper\GitCommand;
use GitWrapper\GitException;
use GitWrapper\GitWrapper;
use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use QualityAssurance\Component\Console\Helper\DrupalInfoFormatHelper;
use QualityAssurance\Component\Console\Helper\ReviewCommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

/**
 * Class DiffMakeFilesCommand
 * @package QualityAssurance\Component\Console\Command
 */
class DiffMakeFilesCommand extends Command
{
  protected function configure()
  {
    $this
      ->setName('diff:make')
      ->setDescription('Check make file for changes.')
      ->addOption('filename', null, InputOption::VALUE_OPTIONAL, 'The filename to check.')
      ->addOption('exclude-dirs', null, InputOption::VALUE_OPTIONAL, 'Directories to exclude.')
      ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Path to recursively check.')
      ->addOption('repository', null, InputOption::VALUE_OPTIONAL, 'Reference repository.')
      ->addOption('branch', null, InputOption::VALUE_OPTIONAL, 'Reference repository.')
      ->addOption('select', null, InputOption::VALUE_NONE, 'Allows you to set which commands to run.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Get the application
    $application = $this->getApplication();

    // Setup the reviewCommandHelper.
    $reviewCommandHelper = new ReviewCommandHelper($input, $output, $application);

    // Parse build properties here. Get the needed params for if the call came
    // from console and not from phing.
    $phingPropertiesHelper = new PhingPropertiesHelper($output);
    $params = $phingPropertiesHelper->requestSettings(array(
      'makefile'             => 'subsite.make',
      'branch'               => 'starterkit.branch',
      'remote'               => 'starterkit.remote',
      'repository'           => 'starterkit.repository',
      'reference_repository' => 'project.reference.repository',
      'reference_branch'     => 'project.reference.branch',
      'reference_remote'     => 'project.reference.remote',
      'repository'           => 'starterkit.repository',
      'basedir'              => 'project.basedir',
    ));

    $params += array(
      'dirname' =>  !empty($input->getOption('directory')) ? $input->getOption('directory') : getcwd(),
    );

    if (!empty($params['makefile']) && pathinfo($params['makefile'], PATHINFO_EXTENSION) !== 'make') {
      return;
    }

    if (empty($params['reference_repository'])) {
      $output->writeln('<comment>Missing property in the  properties file: </comment><info>project.reference.repository</info>');
      return;
    }

    // Find site.make in resources folder.
    $searches = array(
      'projects' => 'modules or themes',
      'libraries' => 'libraries',
    );

    // Update remote repository
    $git = $reviewCommandHelper->setGitWrapper($params);

    $branches = $git->getBranches();
    $head = $branches->head();
    $git->fetch($params['reference_remote']);

    // Build the diff between local file and remote reference.
    $diff = $git->diff($head, $params['reference_remote'] . '/' . $params['reference_branch']);

    $filtered_diff = str_replace('"', '', $diff->getOutput());
    $master = DrupalInfoFormatHelper::drupalParseInfoFormat($git->show($params['reference_remote'] . '/' . $params['reference_branch'] . ':' . str_replace(getcwd(). '/', '', $params['makefile'])));
    $current = DrupalInfoFormatHelper::drupalParseInfoFormat(file_get_contents($params['makefile']));

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
        $output->writeln('<comment>Added ' . $subject . ' found: </comment><info>' . count($added_items) . " found.</info>");
        $output->writeln(preg_replace('/^/m', '+', $added_string));
      }
      // Check for removed projects or libraries.
      if (!empty($removed_items)) {
        $removed_string = DrupalInfoFormatHelper::transformArrayIntoInfoFormat(array($search => $removed_items));
        $output->writeln('<comment>Removed ' . $subject . ' found: </comment><info>' . count($removed_items) . " found.</info>");
        $output->writeln(preg_replace('/^/m', '-', $removed_string));
      }

      // Check for altered projects or libraries.
      $regex = '~^[\+|\-]' . $search . '\[(' . implode(')\].*?$|^[\+|\-]' . $search . '\[(', $untouched_items) . ')\].*?$~m';
      if (!is_null(preg_match_all($regex, $filtered_diff, $matches)) && !empty($matches[0])) {
        // Filter out empty arrays.
        $changed = array_map('array_filter', $matches);
        $changed = array_values(array_filter(array_values($changed)));
        $output->writeln('<comment>Altered ' . $subject . ' found: </comment><info>' . count($changed[1]) . " found.</info>");
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
