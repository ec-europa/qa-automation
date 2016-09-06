<?php

/**
 * File for automated Quality Assurance checks.
 *
 * @file
 * Contains NextEuropa\Phing\QualityAssuranceTask.
 */

namespace NextEuropa\Phing;

use Symfony\Component\Finder\Finder;
use GitWrapper\GitException;
use GitWrapper\GitWrapper;

require_once 'phing/Task.php';

/**
 * Quality assurance task class.
 */
class QualityAssuranceTask extends \Task
{

    /**
     * Used for printing colors.
     */
    const COLORS = array(
        'blue' => "\e[0;34m",
        'cyan' => "\e[0;36m",
        'green' => "\e[0;32m",
        'magenta' => "\e[0;35m",
        'nocolor' => "\e[0m",
        'red' => "\e[1;31m",
        'yellow'  => "\e[0;33m"
    );
    /**
     * Used for printing seperators.
     */
    const SEPERATOR = array(
      'double' => "======================================================================" . PHP_EOL,
      'single' => PHP_EOL . "----------------------------------------------------------------------"
    );

    /**
     * The task attributes.
     */
    protected $passbuild = true;

    /**
     * The setter for the attribute "autoSelect".
     *
     * @param boolean $boolean wether or not to run the entire codebase.
     *
     * @return void
     */
    public function setAutoSelect($boolean)
    {
        $this->autoSelect = $boolean;
    }

    /**
     * The setter for the attribute "makeFile".
     *
     * @param string $string The location of the make file.
     *
     * @return void
     */
    public function setMakeFile($string)
    {
        $this->makeFile = $string;
    }

    /**
     * The setter for the attribute "projectBaseDir".
     *
     * @param string $string The location of the project base folder.
     *
     * @return void
     */
    public function setProjectBaseDir($string)
    {
        $this->projectBaseDir = $string;
    }


    /**
     * The setter for the attribute "resourcesDir".
     *
     * @param string $string The location of the resources folder.
     *
     * @return void
     */
    public function setResourcesDir($string)
    {
        $this->resourcesDir = $string;
    }

    /**
     * The setter for the attribute "distBuildDir".
     *
     * @param string $string The location of the build folder to QA.
     *
     * @return void
     */
    public function setDistBuildDir($string)
    {
        $this->distBuildDir = $string;
    }

    /**
     * The setter for the attribute "libDir".
     *
     * @param string $string The location of the lib folder.
     *
     * @return void
     */
    public function setLibDir($string)
    {
        $this->libDir = $string;
    }

    /**
     * The main entry point method.
     *
     * @return void
     *
     * @throws \BuildException
     *   Thrown when at least one QA check failed and failBuild is set to true.
     */
    public function main()
    {
        // Find all info files in our build folder.
        $finder = new Finder();
        $finder->files()
          ->name('*.info')
          ->in($this->distBuildDir)
          ->exclude(array('contrib', 'contributed'))
          ->sortByName();
        // Loop over files and extract the info files.
        $i = 1;
        $options = array();
        echo SELF::COLORS['magenta'] . "     0) Select all" . PHP_EOL;
        foreach ($finder as $file) {
            $filepathname = $file->getRealPath();
            $filename = basename($filepathname);
            echo "     " . $i . ") " . $filename, PHP_EOL;
            $options[$i] = $filepathname;
            $i++;
        }
        // Stop for selection of module if autoselect is disabled.
        echo SELF::COLORS['nocolor'] . PHP_EOL;
        $selected = $options;
        if (!$this->autoSelect) {
            $qa_selection = readline(
              'Select features, modules and/or themes to QA (seperate with space): '
            );
            if ($qa_selection != "0") {
                $qa_selection = explode(' ', $qa_selection);
                $selected = array_intersect_key($options, array_flip($qa_selection));
            }
        }
        echo PHP_EOL;
        
        // Start the QA on selected features, modules and/or themes.
        $this->startQa($selected);

        // If an error was discovered, fail the build.
        if (!$this->passbuild) {
            throw new \BuildException(
              'Build failed because the code did not pass quality assurance checks.'
            );
        }
    }

    /**
     * Check for new update hook(s).
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    public function checkGitDiffUpdateHook($pathinfo)
    {
        // Find file in lib folder
        $filename = $pathinfo['filename'];
        $updates = 0;
        // Find our install file in the lib folder
        $finder = new Finder();
        $finder->files()
          ->name($filename . '.install')
          ->in($this->libDir);
        $iterator = $finder->getIterator();
        $iterator->rewind();
        if ($file = $iterator->current()) {

            // Get a diff of current branch and master for the install in lib folder.
            $filepath = $file->getRealPath();
            $wrapper = new GitWrapper();
            $git = $wrapper->workingCopy($this->libDir);
            $branches = $git->getBranches();
            $head = $branches->head();
            $diff = $git->diff('master', $head, $filepath);

            // Find new hook update functions in diff.
            $regex = '~' . $filename . '_update_7\d{3}~';
            $contents = is_file($filepath) ? file_get_contents($filepath) : '';
            preg_match_all($regex, $diff, $matches);
            $updates = $matches[0];
            $count = count($updates);
        }

        // Print result.
        echo SELF::COLORS['cyan'] . PHP_EOL . "Check for new updates in branch: ";
        if (empty($updates)) {
            echo SELF::COLORS['green'] . "none found." . SELF::COLORS['nocolor'];
        } else {
            if ($count === 1) {
                echo SELF::COLORS['yellow'] . "1 update found.";
            } else {
                echo SELF::COLORS['red'] . $count . " updates found.";
                $this->passbuild = false;
            }
            // Print the found hooks with file and line number.
            preg_match_all('~' . implode('|', $updates) . '~', $contents, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[0] as $key => $match) {
                list($before) = str_split($contents, $match[1]);
                $line_number = strlen($before) - strlen(str_replace("\n", "", $before)) + 1;
                echo SELF::COLORS['nocolor'] . PHP_EOL . "  ./" . $filename . '.install:' . $line_number . ':' . $match[0];
            }
        }
    }

    /**
     * Check for bypassed code.
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    public function checkBypassCodingStandards($pathinfo)
    {
        // Find codingStandardsIgnore tags.
        $dirname = $pathinfo['dirname'];
        echo SELF::COLORS['cyan'] . PHP_EOL . "Check for coding standard ignores: ";
        $search_for = array(
          '@codingStandardsIgnoreStart',
          '@codingStandardsIgnoreFile',
          '@codingStandardsIgnoreLine'
        );
        $search_pattern = implode('|', $search_for);
        if (exec("grep -IPrino '{$search_pattern}' {$dirname}", $results)) {
            $plural = count($results) > 1 ? 's' : '';
            echo SELF::COLORS['yellow'] .
              count($results) . " ignore" . $plural . " found." .
              SELF::COLORS['nocolor'];
            foreach ($results as $result) {
                $lines = explode(':', str_replace($dirname, '', $result));
                echo PHP_EOL . "  ." . implode(':', array_map('trim', $lines));
            }
        } else {
            echo SELF::COLORS['green'] . "none found." . SELF::COLORS['nocolor'];
        }
    }

    /**
     * Grab all todo's from the file.
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    public function checkTodos($pathinfo)
    {
        // Find todo tags.
        $dirname = $pathinfo['dirname'];
        echo SELF::COLORS['cyan'] . PHP_EOL . "Check for todo's for this release: ";
        $search_for = array(
          '@todo: .*?MULTISITE-[0-9]{5}.*?'
        );
        $search_pattern = implode('|', $search_for);
        if (exec("grep -IPrino '{$search_pattern}' {$dirname}", $results)) {
            $plural = count($results) > 1 ? '\'s' : '';
            echo SELF::COLORS['yellow'] .
              count($results) . " todo" . $plural . " found." .
              SELF::COLORS['nocolor'];
            foreach ($results as $result) {
                $lines = explode(':', str_replace($dirname, '', $result));
                echo PHP_EOL . "  ." . implode(':', array_map('trim', $lines));
            }
        } else {
            echo SELF::COLORS['green'] . "none found." . SELF::COLORS['nocolor'];
        }
    }

    /**
     * Perform a PHPCS on the specified folder.
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    public function checkCodingStandards($pathinfo)
    {
        // Set directories.
        $dirname = $pathinfo['dirname'];
        // Execute phpcs on the module folder.
        echo SELF::COLORS['cyan'] . PHP_EOL . "Check for coding standards: " . SELF::COLORS['nocolor'];
        ob_start();
        passthru('./bin/phpcs --standard=phpcs.xml ' . $dirname, $error);
        $phpcs = ob_get_contents();
        ob_end_clean();
        // Print result.
        if ($error) {
            echo $phpcs;
            $this->passbuild = false;
        } else {
            echo SELF::COLORS['green'] . "no violations.";
        }
    }

    /**
     * Check if a cron is implemented.
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    public function checkCron($pathinfo)
    {
        // Find cron implementation.
        $dirname = $pathinfo['dirname'];
        $filename = $pathinfo['filename'];
        echo SELF::COLORS['cyan'] . "Check for cron implementations: ";
        $search_pattern = $filename . '_cron';
        if (exec("grep -IPrino '{$search_pattern}' {$dirname}", $results)) {
            echo SELF::COLORS['yellow'] . "hook found." . SELF::COLORS['nocolor'];
            foreach ($results as $result) {
                echo PHP_EOL . "  ." . str_replace($dirname, '', $result);
            }
        } else {
            echo SELF::COLORS['green'] . "none found.";
        }
    }

    /**
     * Check for new or removed module(s).
     *
     * @param string $makefile The makefile to check.
     *
     * @return void
     */
    public function checkGitDiffSiteMake($makefile)
    {
        // Find site.make in resources folder
        $searches = array(
          'projects' => 'modules or themes',
          'libraries' => 'libraries'
        );
        // Get a diff of current branch and master.
        $wrapper = new GitWrapper();
        $git = $wrapper->workingCopy($this->resourcesDir);
        $branches = $git->getBranches();
        $head = $branches->head();
        $diff = $git->diff('master', $head, $makefile);

        // Find new projects or libraries.
        foreach ($searches as $search => $subject) {
            $regex = "~\+$search\[(.*?)\]~i";
            preg_match_all($regex, $diff, $matches);
            $additions = array_unique($matches[1]);

            // Print result.
            echo SELF::COLORS['cyan'] . 'New ' . $subject . ' found: ';
            if (empty($additions)) {
                echo SELF::COLORS['green'] . "none found." . PHP_EOL;
            } else {
                echo SELF::COLORS['yellow'] . implode(', ', $additions) . "." . PHP_EOL;
            }
        }
    }
}
