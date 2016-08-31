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
    const RED = "\e[1;31m";
    const GREEN = "\e[0;32m";
    const YELLOW  = "\e[0;33m";
    const BLUE = "\e[0;34m";
    const MAGENTA = "\e[0;35m";
    const CYAN = "\e[0;36m";
    const NOCOLOR = "\e[0m";
    const SEPERATOR =
      "======================================================================\n";

    /**
     * The task attributes.
     */
    protected $directory = null;
    protected $failbuild = true;
    protected $passbuild = true;

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
     * The init method: Do init steps.
     *
     * @return void
     */
    public function init()
    {
        // nothing to do here
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
        // Change to the build directory.
        chdir($this->distBuildDir);
        // Find all info files in our build folder.
        $finder = new Finder();
        $finder->files()
          ->name('*.info')
          ->in($this->distBuildDir)
          ->exclude(array('contrib', 'contributed'));
        // Loop over files and extract the info files.
        $i = 1;
        $options = array();
        echo SELF::MAGENTA . "     0) Select all\n";
        foreach ($finder as $file) {
            $filepath = $file->getRelativePathname();
            $filepathname = $file->getRealPath();
            $extension = pathinfo($filepathname, PATHINFO_EXTENSION);
            $filename = basename($filepathname);
            if ($extension == "info") {
                echo "     " . $i . ") " . $filename, PHP_EOL;
                $options[$i] = $filepath;
                $i++;
            }
        }
        // Stop for selection of module.
        echo SELF::NOCOLOR . "\n";
        $qa_selection = readline(
          'Select features, modules and/or themes to QA (seperate with space): '
        );
        if ($qa_selection != "0") {
            $qa_selection = explode(' ', $qa_selection);
            $qa_selection = array_intersect_key($options, array_flip($qa_selection));
        } else {
            $qa_selection = $options;
        }
        echo "\n";
        // Start the QA on selected features, modules and/or themes.
        $this->startQa($qa_selection);
        if (!$this->passbuild) {
            throw new \BuildException(
              'Build failed because the code did not pass quality assurance 
                    checks.'
            );
        }
    }
    /**
     * Function to start the quality assurance checks.
     *
     * @param array $options Array containing the filepathnames.
     *
     * @return void
     */
    public function startQa($options)
    {
        foreach ($options as $filepathname) {
            // Set variables.
            $file = file_get_contents($filepathname);
            $parsed = $this->_drupalParseInfoFormat($file);
            $pathinfo = pathinfo($filepathname);
            $isFeature = isset($parsed['features']['features_api']) ? true : false;
            // Print header of module, feature or theme.
            echo "\n";
            echo SELF::NOCOLOR . SELF::SEPERATOR;
            echo $this->distBuildDir . "/" . $pathinfo['dirname'] . "\n";
            echo SELF::NOCOLOR . SELF::SEPERATOR;
            $this->_checkCron($pathinfo);
            $this->_checkGitDiffUpdateHook($pathinfo);
            $this->_checkBypassCodingStandards($pathinfo);
            $this->_checkCodingStandards($pathinfo);
            echo "\n";
        }
        $this->_checkGitDiffSiteMake();
    }

    /**
     * Check for new update hook(s).
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    private function _checkGitDiffUpdateHook($pathinfo)
    {
        // Find file in lib folder
        $filename = $pathinfo['filename'];
        $finder  = new Finder();
        $finder->files()
          ->name($filename . '.install')
          ->in($this->libDir);
        foreach ($finder as $file) {
            $filepathname = $file->getPathname;
        }
        // Get a diff of current branch and master.
        $wrapper = new GitWrapper();
        $git = $wrapper->workingCopy($this->libDir);
        $branches = $git->getBranches();
        $head = $branches->head();
        $diff =  $git->diff('master', $head, $filepathname);

        // Find new hook update functions in diff.
        $regex = '~' . $filename . '_update_7\d{3}~';
        preg_match_all($regex, $diff, $matches);
        $updates = $matches[0];

        // Print result.
        echo SELF::NOCOLOR . "\nNew updates found in this branch: ";
        if (empty($updates)) {
            echo SELF::GREEN . "none found." . SELF::NOCOLOR;
        } elseif (count($updates) === 1) {
            echo SELF::YELLOW . $updates[0];
        } else {
            echo SELF::RED . "multiple found!" . SELF::NOCOLOR;
            $this->passbuild = false;
        }
    }

    /**
     * Check for bypassed code.
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    private function _checkBypassCodingStandards($pathinfo)
    {
        // Find codingStandardsIgnore tags.
        $dirname = $pathinfo['dirname'];
        echo SELF::NOCOLOR . "\nCheck for coding standard ignores: ";
        $search_for = array(
          '@codingStandardsIgnoreStart',
          '@codingStandardsIgnoreFile',
          '@codingStandardsIgnoreLine'
        );
        $search_pattern = implode('|', $search_for);
        if (exec("grep -IPrinoz '{$search_pattern}' {$dirname}", $results)) {
            $plural = count($results) > 1 ? 's' : '';
            echo SELF::YELLOW .
              count($results) . " ignore" . $plural . " found." .
              SELF::NOCOLOR;
            foreach ($results as $result) {
                echo "\n  ." . str_replace($dirname, '', $result);
            }
        } else {
            echo SELF::GREEN . "none found." . SELF::NOCOLOR;
        }
//      // Display codingstandardsignore.
//      $search_pattern = '(?<=codingStandardsIgnoreStart).*(\n|.)*(?=codingStandardsIgnoreEnd)';
//      if (exec("grep -IPrinoz '{$search_pattern}' {$dirname}", $results)) {
//        foreach ($results as $result) {
//          echo "\n  ." . str_replace($dirname, '', $result);
//        }
//      } else {
//        echo SELF::GREEN . "none found.";
//      }
    }

    /**
     * Perform a PHPCS on the specified folder.
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    private function _checkCodingStandards($pathinfo)
    {
        // Change directory to project root to run phpcs from.
        chdir($this->projectBaseDir);
        // Set directories.
        $dirname = $pathinfo['dirname'];
        $standards = $this->projectBaseDir . '/vendor/drupal/coder/coder_sniffer/';
        // Execute phpcs on the module folder.
        echo "\nCheck coding standards: ";
        $phpcs = shell_exec('./bin/phpcs --standard=phpcs.xml ' .
          $this->distBuildDir . '/' . $dirname);
        // Print result.
        if (!empty(trim($phpcs))) {
            echo "\n" . $phpcs;
            $this->passbuild = false;
        } else {
            echo SELF::GREEN . "no violations.";
        }

        chdir($this->distBuildDir);
    }

    /**
     * Check if a cron is implemented.
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    private function _checkCron($pathinfo)
    {
        // Find cron implementation.
        $dirname = $pathinfo['dirname'];
        $filename = $pathinfo['filename'];
        echo SELF::NOCOLOR . "\nCheck for cron implementations: ";
        $search_pattern = $filename . '_cron';
        if (exec("grep -IPrin '{$search_pattern}' {$dirname}", $results)) {
            echo SELF::YELLOW . "hook found." . SELF::NOCOLOR;
            foreach ($results as $result) {
                echo "\n  ." . str_replace($dirname, '', $result);
            }
        } else {
            echo SELF::GREEN . "none found.";
        }
    }

    /**
     * Check for new or removed module(s).
     *
     * @return void
     */
    private function _checkGitDiffSiteMake()
    {
        // Find site.make in resources folder
        $searches = array(
          'projects' => 'modules or themes',
          'libraries' => 'libraries'
        );
        $finder  = new Finder();
        $finder->files()
          ->name('site.make')
          ->in($this->resourcesDir);
        foreach ($finder as $file) {
            $filepathname = $file->getPathname;
        }
        // Get a diff of current branch and master.
        $wrapper = new GitWrapper();
        $git = $wrapper->workingCopy($this->resourcesDir);
        $branches = $git->getBranches();
        $head = $branches->head();
        $diff =  $git->diff('master', $head, $filepathname);

        echo "\n";
        echo SELF::NOCOLOR . SELF::SEPERATOR;
        echo SELF::NOCOLOR .
          str_replace($this->projectBaseDir, '.', $this->resourcesDir) . "/site.make\n";
        echo SELF::NOCOLOR . SELF::SEPERATOR;

        // Find new projects or libraries.
        foreach ($searches as $search => $subject) {
            $regex = "~\+$search\[(.*?)\]~i";
            preg_match_all($regex, $diff, $matches);
            $additions = array_unique($matches[1]);

            // Print result.
            echo SELF::NOCOLOR . 'New ' . $subject . ' found: ';
            if (empty($additions)) {
                echo SELF::GREEN . "none found.\n";
            } else {
                echo SELF::YELLOW . implode(', ', $additions) . ".\n";
            }
        }
    }

    /**
     * Parses data in Drupal's .info format.
     *
     * @param string $data A string to parse.
     *
     * @return array $info The info array.
     *
     * @link https://api.drupal.org/api/drupal/includes%21common.inc/function/drupal_parse_info_format/7.x
     */
    private function _drupalParseInfoFormat($data)
    {
        $info = array();

        if (preg_match_all(
          '@^\s*((?:[^=;\[\]]|\[[^\[\]]*\])+?)\s*=\s*(?:("(?:[^"]|(?<=\\\\)")*")|(
            \'(?:[^\']|(?<=\\\\)\')*\')|([^\r\n]*?))\s*$@msx',
          $data,
          $matches,
          PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                // Fetch the key and value string.
                $i = 0;
                foreach (array('key', 'value1', 'value2', 'value3') as $var) {
                    $$var = isset($match[++$i]) ? $match[$i] : '';
                }
                $value = stripslashes(substr($value1, 1, -1)) .
                  stripslashes(substr($value2, 1, -1)) .
                  $value3;

                // Parse array syntax.
                $keys = preg_split('/\]?\[/', rtrim($key, ']'));
                $last = array_pop($keys);
                $parent = &$info;

                // Create nested arrays.
                foreach ($keys as $key) {
                    if ($key == '') {
                        $key = count($parent);
                    }
                    if (!isset($parent[$key]) || !is_array($parent[$key])) {
                        $parent[$key] = array();
                    }
                    $parent = &$parent[$key];
                }

                // Handle PHP constants.
                if (preg_match('/^\w+$/i', $value) && defined($value)) {
                    $value = constant($value);
                }

                // Insert actual value.
                if ($last == '') {
                    $last = count($parent);
                }
                $parent[$last] = $value;
            }
        }

        return $info;
    }
}
