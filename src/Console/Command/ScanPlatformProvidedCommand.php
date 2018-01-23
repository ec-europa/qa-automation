<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\ScanPlatformProvidedCommand.
 */

namespace QualityAssurance\Component\Console\Command;

use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use QualityAssurance\Component\Console\Helper\DrupalInfoFormatHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ScanPlatformProvidedCommand
 * @package QualityAssurance\Component\Console\Command
 */
class ScanPlatformProvidedCommand extends Command
{
    protected function configure()
    {
        $phingPropertiesHelper = new PhingPropertiesHelper(new NullOutput());
        $properties = $phingPropertiesHelper->requestSettings(array(
          'profile' => 'profile',
          'resources.dir.site.make' => 'resources.dir.site.make',
          'project.basedir' => 'project.basedir',
        ));

        $this
            ->setName('scan:mkpd')
            ->setDescription('Scan for platform provided modules.')
            ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Path to recursively check.')
            ->addOption('exclude-dirs', null, InputOption::VALUE_OPTIONAL, 'Directories to exclude.')
            ->addOption('filename', null, InputOption::VALUE_OPTIONAL, 'Modulename.', $properties['resources.dir.site.make'])
            ->addOption('profile', null, InputOption::VALUE_OPTIONAL, 'Profile.', $properties['profile']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Find todos tags.
        // @codingStandardsIgnoreStart
        $dirname = !empty($input->getOption('directory')) ? $input->getOption('directory') : getcwd();
        $exclude_dirs = !empty($input->getOption('exclude-dirs')) ? explode(',', $input->getOption('exclude-dirs')) : null;
        $exclude_dir = is_array($exclude_dirs) ? '--exclude-dir=' . implode(' --exclude-dir=', $exclude_dirs) . ' ' : '';
        $filename = !empty($input->getOption('filename')) ? $input->getOption('filename') : '@todo';
        $profile = !empty($input->getOption('profile')) ? $input->getOption('profile') : '@todo';
        // @codingStandardsIgnoreEnd

        if (!empty($filename) && pathinfo($filename, PATHINFO_EXTENSION) !== 'make') {
            return;
        }

        // Find site.make in resources folder.
        $makefile = $filename;
        $searches = array(
            'projects' => 'modules or themes',
            'libraries' => 'libraries',
        );
        $duplicates = array();
        // Get the make file of the profile.
        $url = 'https://raw.githubusercontent.com/ec-europa/platform-dev/master/resources/' . $profile . '.make';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if ($data = curl_exec($curl)) {
            // Get the contents of the profile make file.
            $profile = DrupalInfoFormatHelper::drupalParseInfoFormat($data);
            // Get the contents of the subsite make file.
            $siteMake = DrupalInfoFormatHelper::drupalParseInfoFormat(file_get_contents($makefile));
            // Search the subsite make file for duplicates.
            foreach ($searches as $search => $subject) {
                // Perform search.
                if (isset($siteMake[$search])) {
                    foreach ($siteMake[$search] as $name => $contents) {
                        if (isset($profile[$search][$name]) && !isset($siteMake[$search][$name]['patch'])) {
                            $duplicates[$search][] = $name;
                        }
                    }
                }
                // Print result.
                if (!empty($duplicates[$search])) {
                    // @codingStandardsIgnoreLine
                    $output->writeln('<comment>Platform ' . $subject . ' found: </comment><info>' . implode(', ', $duplicates[$search]) . '</info>');
                    $output->writeln('');
                }
            }
        }
        curl_close($curl);
    }
}
