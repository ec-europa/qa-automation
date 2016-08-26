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
//            // Check info files.
//            $this->_checkMandatoryProperties($parsed);
//            $this->_checkForbiddenProperties($parsed);
//            $this->_checkForbiddenDependencies($parsed);
//            $this->_checkFeaturesVersion($pathinfo, $parsed);
//            echo "\n";
//            // Check other files.
//            if ($isFeature) {
//                $this->_checkFields($pathinfo);
//                $this->_checkPermissions($pathinfo);
//            }
            $this->_checkCron($pathinfo);
//            $this->_checkNonDrupalWrappedFunctions($pathinfo);
            $this->_checkGitDiffUpdateHook($pathinfo);
            $this->_checkBypassCodingStandards($pathinfo);
            $this->_checkCodingStandards($pathinfo);
            echo "\n";
        }
        $this->_checkGitDiffSiteMake();
    }
    /**
     * Check mandatory properties in info file.
     *
     * @param array $parsed The parsed info file in array format.
     *
     * @return void
     */
    private function _checkMandatoryProperties($parsed)
    {
        $properties = array("multisite_version", "name", "description", "core", "php");
        foreach ($properties as $property) {
            if (isset($parsed[$property])) {
                $property_value_trimmed = strlen($parsed[$property]) > 53 ?
                  substr($parsed[$property], 0, 53)."..." :
                  $parsed[$property];
                echo SELF::NOCOLOR .
                  $property . " = " .
                  $property_value_trimmed .
                  "\n";
            } else {
                echo SELF::RED .
                  "property " .
                  $property .
                  " missing!\n" .
                  SELF::NOCOLOR;
                $this->passbuild = false;
            }
        }
    }
    /**
     * Check forbidden properties in info file.
     *
     * @param array $parsed The parsed info file in array format.
     *
     * @return void
     */
    private function _checkForbiddenProperties($parsed)
    {
        $properties = array("project", "version");
        foreach ($properties as $property) {
            if (isset($parsed[$property])) {
                echo SELF::NOCOLOR .
                  $property . " = " .
                  $parsed[$property] .
                  SELF::RED .
                  " (must be removed!)\n" .
                  SELF::NOCOLOR;
                $this->passbuild = false;
            }
        }
    }

    /**
     * Check forbidden dependencies in info file.
     *
     * @param array $parsed The parsed info file in array format.
     *
     * @return void
     */
    private function _checkForbiddenDependencies($parsed)
    {
        $props = array("menu", "php");
        $deps = isset($parsed['dependencies']) && is_array($parsed['dependencies']) ?
          $parsed['dependencies'] :
          array();
        foreach ($props as $property) {
            if (in_array($property, $deps)) {
                echo SELF::NOCOLOR .
                  "dependencies[] = " .
                  $property . SELF::RED .
                  " (must be removed!)\n" .
                  SELF::NOCOLOR;
                $this->passbuild = false;
            }
        }
    }

    /**
     * Check features api version in info if it is a feature.
     *
     * @param array $pathinfo The pathinfo on which to do checks.
     * @param array $parsed   The parsed info file in array format.
     *
     * @return void
     */
    private function _checkFeaturesVersion($pathinfo, $parsed)
    {
        $dirname = $pathinfo['dirname'];
        $filename = $pathinfo['filename'];
        $features_api = isset($parsed['features']['features_api']) ?
          $parsed['features']['features_api'] :
          false;
        if (file_exists($dirname . "/" . $filename . '.features.inc')
          && $api_version = reset($features_api)
        ) {
            if ($api_version == "api:1") {
                echo SELF::NOCOLOR .
                  "features[features_api][] = " .
                  $api_version . SELF::RED .
                  " (must be upgraded!)" .
                  SELF::NOCOLOR;
                $this->passbuild = false;
            } elseif ($api_version == "api:2") {
                echo SELF::NOCOLOR .
                  "features[features_api][] = " .
                  $api_version .
                  SELF::NOCOLOR;
            }
        }
    }

    /**
     * Check if all fields are locked and date fields are datestamp.
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    private function _checkFields($pathinfo)
    {
        $dirname = $pathinfo['dirname'];
        $filename = $pathinfo['filename'];
        $dirname_filename = $dirname . "/" . $filename;
        $field_base_inc_path =  $dirname_filename . '.features.field_base.inc';
        $has_unlocked_fields = false;
        $has_non_datestamp_field = false;
        if (file_exists($field_base_inc_path)) {
            include_once $field_base_inc_path;
            $field_bases = call_user_func($filename . '_field_default_field_bases');
            echo SELF::NOCOLOR . "\nCheck if all fields are locked: ";
            foreach ($field_bases as $field_name => $field_base) {
                if ($field_base['locked'] == 0) {
                    echo SELF::NOCOLOR .
                      "\n  " . $field_name .
                      " = " . SELF::RED .
                      "not locked!" .
                      SELF::NOCOLOR;
                    $has_unlocked_fields = true;
                    $this->passbuild = false;
                }
            }
            if (!$has_unlocked_fields) {
                echo SELF::GREEN . "all locked." . SELF::NOCOLOR;
            }
            echo SELF::NOCOLOR . "\nCheck if all date fields are datestamp: ";
            foreach ($field_bases as $field_name => $field_base) {
                if ($field_base['module'] == "date"
                  && $field_base['type'] != "datestamp"
                ) {
                    echo SELF::NOCOLOR .
                      "\n  " . $field_name .
                      " = " . SELF::RED .
                      "is " . $field_base['type'] . "!" .
                      SELF::NOCOLOR;
                    $has_non_datestamp_fields = true;
                    $this->passbuild = false;
                }
            }
            if (!$has_non_datestamp_fields) {
                echo SELF::GREEN . "all datestamp." . SELF::NOCOLOR;
            }
        } else {
            echo SELF::NOCOLOR .
              "\nCheck if all fields are locked: " .
              SELF::YELLOW .
              "no fields present.";
            echo SELF::NOCOLOR .
              "\nCheck if all date fields are datestamp: " .
              SELF::YELLOW .
              "no fields present.";
        }
    }

    /**
     * Check if all risky permissions have been removed.
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    private function _checkPermissions($pathinfo)
    {
        $dirname = $pathinfo['dirname'];
        $filename = $pathinfo['filename'];
        $dirname_filename = $dirname . "/" . $filename;
        $user_permission_inc_path = $dirname_filename .
          '.features.user_permission.inc';
        $risky_permissions = array(
          'administer modules',
          'administer software updates',
          'administer permissions',
          'administer features',
          'manage features',
          'administer ckeditor lite',
          'administer jquery update',
          'access devel information',
          'execute php code'
        );
        $has_risky_permissions = false;
        if (file_exists($user_permission_inc_path)) {
            include_once $user_permission_inc_path;
            $permissions = call_user_func($filename . '_user_default_permissions');
            echo SELF::NOCOLOR . "\nCheck for risky permissions: ";
            foreach ($permissions as $permission) {
                if (in_array($permission['name'], $risky_permissions)) {
                    echo SELF::NOCOLOR.
                      "\n  " . $permission['name'] .
                      " " . SELF::RED ."(needs to be removed)!" .
                      SELF::NOCOLOR;
                    $has_risky_permissions = true;
                    $this->passbuild = false;
                }
            }
            if (!$has_risky_permissions) {
                echo SELF::GREEN . "none present." . SELF::NOCOLOR;
            }
        } else {
            echo SELF::NOCOLOR .
              "\nCheck for risky permissions: " .
              SELF::YELLOW . "no permissions.";
        }
    }

    /**
     * Check for non Drupal wrapped functions.
     *
     * @param array $pathinfo The pathinfo of the info file.
     *
     * @return void
     */
    private function _checkNonDrupalWrappedFunctions($pathinfo)
    {
        $dirname = $pathinfo['dirname'];
        $functions = array(
          'basename', 'chmod', 'dirname', 'http_build_query', 'json_decode',
          'json_encode', 'mkdir', 'move_uploaded_file', 'parse_url', 'realpath',
          'register_shutdown_function', 'rmdir', 'session_regenerate',
          'session_start', 'set_time_limit', 'strlen', 'strtolower', 'strtoupper',
          'substr', 'tempnam', 'ucfirst', 'unlink', 'xml_parser_create'
        );
        $search_pattern = '(?<!drupal_)' .
          implode('\(|(?<!drupal_)', $functions) .
          '\(';
        echo SELF::NOCOLOR . "\nNon Drupal wrapper functions: ";
        if (exec("grep -IPrino '{$search_pattern}' {$dirname}", $results)) {
            $plural = count($results) > 1 ? 's' : '';
            echo SELF::RED .
              count($results) . " function" . $plural . " found." .
              SELF::NOCOLOR;
            foreach ($results as $result) {
                echo "\n  ." . str_replace($dirname, '', $result);
            }
            $this->passbuild = false;
        } else {
            echo SELF::GREEN . "none found.";
        }
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
        $phpcs = shell_exec('./bin/phpcs --standard=phpcs-ruleset.xml ' .
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
