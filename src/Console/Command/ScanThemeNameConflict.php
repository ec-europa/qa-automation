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
use QualityAssurance\Component\Console\Helper\ReviewCommandThemeHelper;

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
      ->addOption('select', null, InputOption::VALUE_OPTIONAL, 'Allows you to set which commands to run.')
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
    if (!$theme_name) {
      $application = $this->getApplication();
      $reviewCommandHelper = new ReviewCommandThemeHelper($input, $output, $application);
      $options = $reviewCommandHelper->getThemeFiles($properties['lib']);
    }
    else {
      $options[] = $theme_name;
    }

    foreach ($options as $theme_name) {
      // cleanup file name.
      $theme_name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $theme_name);
      $duplicates = array();

      // Search duplicate file name in the project.
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

      if (count($duplicates) > 0) {
        $output->writeln("<comment>Theme: </comment><info>found conflict with theme name.</info>");
        foreach ($duplicates as $path => $name) {
          $output->writeln($name . ": " . $path);
        }
      }
    }
  }

}