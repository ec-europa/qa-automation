<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\ScanThemeNameConflict.
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
 * Class ScanThemeNameConflict
 * @package QualityAssurance\Component\Console\Command
 */
class ScanThemeNameConflict extends Command
{
  protected function configure()
  {
    $this
      ->setName('theme:conflict')
      ->setDescription('Scan for duplicated theme name in modules or features.')
      ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Path to recursively check.')
      ->addOption('exclude-dirs', null, InputOption::VALUE_OPTIONAL, 'Directories to exclude.')
      ->addOption('filename', null, InputOption::VALUE_OPTIONAL, 'Modulename.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $phingPropertiesHelper = new PhingPropertiesHelper($output);
    $properties = $phingPropertiesHelper->requestSettings(array(
      'lib' => 'subsite.resources.lib.dir',
    ));

    // Get the current theme name.
    $theme_name = $input->getOption('filename');

    $duplicates = array();
    // Find all info files in provided path.
    $finder = new Finder();
    $finder->files()
      ->name($theme_name . '.info')
      ->in($properties['lib'])
      ->exclude(array('themes'))
      ->sortByName();

    // Loop over files and build an options array.
    foreach ($finder as $file) {
      $filepathname = $file->getRealPath();
      $filename = basename($filepathname);
      $duplicates[$filepathname] = $filename;
    }

    // Find todos tags.
    $dirname = !empty($input->getOption('directory')) ? $input->getOption('directory') : getcwd();
    $exclude_dirs = !empty($input->getOption('exclude-dirs')) ? explode(',', $input->getOption('exclude-dirs')) : NULL;
    $exclude_dir = is_array($exclude_dirs) ? '--exclude-dir=' . implode(' --exclude-dir=', $exclude_dirs) . ' ' : '';
    $filename = !empty($input->getOption('filename')) ? $input->getOption('filename') : '@todo';

    $search_pattern = $filename . '_cron';

    if (count($duplicates) > 0) {
      $output->writeln("<comment>Theme: </comment><info>found conflict with theme name.</info>");
      foreach ($duplicates as $path => $name) {
        $output->writeln(str_replace($dirname, '.', $path . ": " . $name));
      }
    }
  }
}