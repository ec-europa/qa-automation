<?php

/**
 * @file
 * File for automated Quality Assurance checks.
 *
 * Contains NextEuropa\Phing\QualityAssuranceTask.
 */

namespace NextEuropa\Phing;

use Symfony\Component\Finder\Finder;
use GitWrapper\GitWrapper;

require_once 'phing/Task.php';

/**
 * Quality assurance task class.
 */
class QualityAssuranceTask extends \Task {

  /**
   * Used for printing colors.
   */
  public static $color = array(
    'blue' => "\e[0;34m",
    'cyan' => "\e[0;36m",
    'green' => "\e[0;32m",
    'magenta' => "\e[0;35m",
    'nocolor' => "\e[0m",
    'red' => "\e[1;31m",
    'yellow'  => "\e[0;33m",
  );

  /**
   * Used for printing seperators.
   */
  public static $separator = array(
    'double' => "======================================================================\r\n",
    'single' => "\r\n----------------------------------------------------------------------",
  );

  /**
   * The task attributes.
   */
  protected $passbuild = TRUE;

  /**
   * The setter for the attribute "skipSelect".
   *
   * @param bool $boolean
   *   Wether or not to run the entire codebase automatically.
   */
  public function setSkipSelect($boolean) {
    $this->skipSelect = $boolean;
  }

  /**
   * The setter for the attribute "skipPhpcs".
   *
   * @param bool $boolean
   *   Wether or not to run Phpcs.
   */
  public function setSkipPhpcs($boolean) {
    $this->skipPhpcs = $boolean;
  }

  /**
   * The setter for the attribute "makeFile".
   *
   * @param string $string
   *   The location of the make file.
   */
  public function setMakeFile($string) {
    $this->makeFile = $string;
  }

  /**
   * The setter for the attribute "projectBaseDir".
   *
   * @param string $string
   *   The location of the project base folder.
   */
  public function setProjectBaseDir($string) {
    $this->projectBaseDir = $string;
  }


  /**
   * The setter for the attribute "resourcesDir".
   *
   * @param string $string
   *   The location of the resources folder.
   */
  public function setResourcesDir($string) {
    $this->resourcesDir = $string;
  }

  /**
   * The setter for the attribute "distBuildDir".
   *
   * @param string $string
   *   The location of the build folder to QA.
   */
  public function setDistBuildDir($string) {
    $this->distBuildDir = $string;
  }

  /**
   * The setter for the attribute "libDir".
   *
   * @param string $string
   *   The location of the lib folder.
   */
  public function setLibDir($string) {
    $this->libDir = $string;
  }

  /**
   * The setter for the attribute "profileName".
   *
   * @param string $string
   *   The name of the platform.
   */
  public function setProfileName($string) {
    $this->profileName = $string;
  }

  /**
   * The main entry point method.
   *
   * @throws \BuildException
   *   Thrown when at least one QA check failed and failBuild is set to true.
   */
  public function main() {
    // Find all info files in our build folder.
    $finder = new Finder();
    $makefile = $this->makeFile;
    $finder->files()
          ->name('*.info')
          ->in($this->distBuildDir)
          ->exclude(array('contrib', 'contributed'))
          ->sortByName();

    $options = array();
    $i = 1;
    echo self::$color['magenta'] . "     0) Select all\r\n";
    // Set the make file(s) as the first option(s).
    // @todo: refractor this to allow multiple make files.
    // @todo: would be nice to have selection on top and display on bottom for this.
    if (is_file($makefile)) {
      $options[$i] =  $makefile;
      echo "     " . $i . ") " . basename($makefile) . "\r\n";
      $i++;
    }
    // Loop over files and extract the info files.
    foreach ($finder as $file) {
      $filepathname = $file->getRealPath();
      $filename = basename($filepathname);
      echo "     " . $i . ") " . $filename . "\r\n";
      $options[$i] = $filepathname;
      $i++;
    }

    // Stop for selection of module if autoselect is disabled.
    echo self::$color['nocolor'] . "\r\n";
    $selected = $options;
    if (!$this->skipSelect) {

      echo "Select features, modules and/or themes to QA (seperate with space): ";
      $handle = fopen("php://stdin", "r");
      $qa_selection = rtrim(fgets($handle, 128));
      fclose($handle);

      if ($qa_selection != "0") {
        $qa_selection = explode(' ', $qa_selection);
        $selected = array_intersect_key($options, array_flip($qa_selection));
      }
    }
    echo "\r\n";

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
   * @param array $pathinfo
   *   The pathinfo of the info file.
   */
  public function checkGitDiffUpdateHook($pathinfo) {
    // Find file in lib folder.
    $filename = $pathinfo['filename'];
    $schema_versions = array();
    // Find our install file in the lib folder.
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

      // Find hook update functions in diff.
      $regex = '~(\+|\-)' . 'function ' . $filename . '_update_' . '(7\d{3})~';
      $contents = is_file($filepath) ? file_get_contents($filepath) : '';
      preg_match_all($regex, $diff, $results);
      $function_names = array_unique(str_replace(array('+', '-'), '', $results[0]));
      $diff_actions = isset($results[1]) ? $results[1] : array();
      $schema_versions = isset($results[2]) ? $results[2] : array();
    }

    // Print result of new updates.
    echo self::$color['cyan'] . "\r\nCheck for new updates in branch: ";
    if (empty($schema_versions)) {
      echo self::$color['green'] . "none found." . self::$color['nocolor'];
    }
    else {
      $count = count(array_unique($schema_versions));
      $plural = $count > 1 ? 's' : '';
      echo self::$color['yellow'] . $count . " update" . $plural . " found.";
      $this->printAllFoundFunctionNames($filename, $function_names, $contents);
    }

    // Print result of removed updates!
    echo self::$color['cyan'] . "\r\nCheck for removed updates in branch: ";
    $function_names = array();
    if (isset($diff_actions)) {
      $count_occurrance = array_count_values($schema_versions);
      foreach ($diff_actions as $key => $action) {
        if ($action == '-' && $count_occurrance[$schema_versions[$key]] == '1') {
          $function_names[] = str_replace('-', '', $results[0][$key]);
        }
      }
    }
    if (empty($function_names)) {
      echo self::$color['green'] . "none found." . self::$color['nocolor'];
    } else {
      $count = count($function_names);
      $plural = $count > 1 ? 's' : '';
      echo self::$color['red'] . $count . " update" . $plural . " found.";
      $this->printAllFoundFunctionNames($filename, $function_names, $contents);
      $this->passbuild = FALSE;
    }
  }

  /**
   * Check for bypassed code.
   *
   * @param array $pathinfo
   *   The pathinfo of the info file.
   */
  public function checkBypassCodingStandards($pathinfo) {
    // Find codingStandardsIgnore tags.
    $dirname = $pathinfo['dirname'];
    echo self::$color['cyan'] . "\r\nCheck for coding standard ignores: ";
    $search_for = array(
      '@codingStandardsIgnoreStart',
      '@codingStandardsIgnoreFile',
      '@codingStandardsIgnoreLine',
    );
    $search_pattern = implode('|', $search_for);
    if (exec("grep -IPrino '{$search_pattern}' {$dirname}", $results)) {
      $plural = count($results) > 1 ? 's' : '';
      echo self::$color['yellow'] .
              count($results) . " ignore" . $plural . " found." .
              self::$color['nocolor'];
      foreach ($results as $result) {
        $lines = explode(':', str_replace($dirname, '', $result));
        echo "\r\n  ." . implode(':', array_map('trim', $lines));
      }
    }
    else {
      echo self::$color['green'] . "none found." . self::$color['nocolor'];
    }
  }

  /**
   * Grab all todo's from the file.
   *
   * @param array $pathinfo
   *   The pathinfo of the info file.
   */
  public function checkTodos($pathinfo) {
    // Find todo tags.
    $dirname = $pathinfo['dirname'];
    echo self::$color['cyan'] . "\r\nCheck for todo's for this release: ";
    $search_for = array(
      '@todo: .*?MULTISITE-[0-9]{5}.*?',
    );
    $search_pattern = implode('|', $search_for);
    if (exec("grep -IPrino '{$search_pattern}' {$dirname}", $results)) {
      $plural = count($results) > 1 ? '\'s' : '';
      echo self::$color['yellow'] .
              count($results) . " todo" . $plural . " found." .
              self::$color['nocolor'];
      foreach ($results as $result) {
        $lines = explode(':', str_replace($dirname, '', $result));
        echo "\r\n  ." . implode(':', array_map('trim', $lines));
      }
    }
    else {
      echo self::$color['green'] . "none found." . self::$color['nocolor'];
    }
  }

  /**
   * Perform a PHPCS on the specified folder.
   *
   * @param array $pathinfo
   *   The pathinfo of the info file.
   */
  public function checkCodingStandards($pathinfo) {
    // Set directories.
    $dirname = $pathinfo['dirname'];
    // Execute phpcs on the module folder.
    echo self::$color['cyan'] . "\r\nCheck for coding standards: " . self::$color['nocolor'];
    ob_start();
    passthru('./bin/phpcs --standard=phpcs.xml ' . $dirname, $error);
    $phpcs = ob_get_contents();
    ob_end_clean();
    // Print result.
    if ($error) {
      echo $phpcs;
      $this->passbuild = FALSE;
    }
    else {
      echo self::$color['green'] . "no violations.\r\n";
    }
  }

  /**
   * Check if a cron is implemented.
   *
   * @param array $pathinfo
   *   The pathinfo of the info file.
   */
  public function checkCron($pathinfo) {
    // Find cron implementation.
    $dirname = $pathinfo['dirname'];
    $filename = $pathinfo['filename'];
    echo self::$color['cyan'] . "Check for cron implementations: ";
    $search_pattern = $filename . '_cron';
    if (exec("grep -IPrino '{$search_pattern}' {$dirname}", $results)) {
      echo self::$color['yellow'] . "hook found." . self::$color['nocolor'];
      foreach ($results as $result) {
        echo "\r\n  ." . str_replace($dirname, '', $result);
      }
    }
    else {
      echo self::$color['green'] . "none found.";
    }
  }

  /**
   * Check for new or removed module(s).
   *
   * @param string $makefile
   *   The makefile to check.
   */
  public function checkGitDiffSiteMake($pathinfo) {
    // Find site.make in resources folder.
    $searches = array(
      'projects' => 'modules or themes',
      'libraries' => 'libraries',
    );
    $makefile = $pathinfo['dirname'] . '/' . $pathinfo['basename'];
    // Get a diff of current branch and master.
    $wrapper = new GitWrapper();
    $git = $wrapper->workingCopy($this->resourcesDir);
    $branches = $git->getBranches();
    $head = $branches->head();
    $diff = $git->diff('master', $makefile, $makefile);
    $filtered_diff = str_replace('"', '', $diff->getOutput());
    $master = $this->drupalParseInfoFormat($git->show('master:' . str_replace(getcwd(). '/', '', $makefile)));
    $current = $this->drupalParseInfoFormat(file_get_contents($makefile));

    // Find new projects or libraries.
    foreach ($searches as $search => $subject) {
      $current_items = isset($current[$search]) ? $current[$search] : array();
      $master_items = isset($master[$search]) ? $master[$search] : array();
      $added_items = array_diff_key($current_items, $master_items);
      $removed_items = array_diff_key($master_items, $current_items);
      $complete_items = array_merge($added_items, $removed_items);
      $untouched_items = array_keys(array_diff_key($current_items, $complete_items));

      // Check for new projects or libraries.
      echo self::$color['cyan'] . 'Added ' . $subject . ' found: ';
      if (!empty($added_items)) {
        $added_string = $this->transformArrayIntoInfoFormat(array($search => $added_items));
        echo self::$color['red'] . count($added_items) . " found." . self::$color['nocolor'] . "\r\n" . preg_replace('/^/m', '+', $added_string);
      }
      else {
        echo self::$color['green'] . "none found.\r\n";
      }
      // Check for removed projects or libraries.
      echo self::$color['cyan'] . 'Removed ' . $subject . ' found: ';
      if (!empty($removed_items)) {
        $removed_string = $this->transformArrayIntoInfoFormat(array($search => $removed_items));
        echo self::$color['red'] . count($removed_items) . " found." . self::$color['nocolor'] . "\r\n" . preg_replace('/^/m', '-', $removed_string);
      }
      else {
        echo self::$color['green'] . "none found.\r\n";
      }

      // Check for altered projects or libraries.
      echo self::$color['cyan'] . 'Altered ' . $subject . ' found: ';
      $regex = '~^[\+|\-]' . $search . '\[(' . implode(')\].*?$|^[\+|\-]' . $search . '\[(', $untouched_items) . ')\].*?$~m';
      if (!is_null(preg_match_all($regex, $filtered_diff, $matches)) && !empty($matches[0])) {
        // Filter out empty arrays.
        $changed = array_map('array_filter', $matches);
        $changed = array_values(array_filter(array_values($changed)));
        echo self::$color['red'] . count($changed[1]) . " found.\r\n";
        foreach ($changed as $key => $changed_array) {
          $changed_array = array_values($changed_array);
          if (!empty($changed_array) && $key != 0) {
            $new_item = array(' ' .$search => array($changed_array[0] => $current_items[$changed_array[0]]));
            $old_item = array(' ' .$search => array($changed_array[0] => $master_items[$changed_array[0]]));
            $new_string = $this->transformArrayIntoInfoFormat($new_item);
            $old_string = $this->transformArrayIntoInfoFormat($old_item);
            $combined_string = implode("\n", array_unique(explode("\n", $old_string . $new_string )));
            foreach ($changed[0] as $replacement) {
              $combined_string = str_replace(' ' . substr($replacement, 1), $replacement, $combined_string);
            }
            echo self::$color['nocolor'] . $combined_string;
          }
        }
      }
      else {
        echo self::$color['green'] . "none found.\r\n";
      }
    }
  }

  /**
   * Check modules, themes or libraries used in the platform.
   *
   * @param string $makefile
   *   The makefile to check.
   */
  public function checkSiteMakeForPlatformDependencies($pathinfo) {
    // Find site.make in resources folder.
    $makefile = $pathinfo['dirname'] . '/' . $pathinfo['basename'];
    $searches = array(
      'projects' => 'modules or themes',
      'libraries' => 'libraries',
    );
    $duplicates = array();
    // Get the make file of the profile.
    $url = 'https://raw.githubusercontent.com/ec-europa/platform-dev/master/resources/' . $this->profileName . '.make';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    if ($data = curl_exec($curl)) {
      // Get the contents of the profile make file.
      $profile = $this->drupalParseInfoFormat($data);
      // Get the contents of the subsite make file.
      $siteMake = $this->drupalParseInfoFormat(file_get_contents($makefile));
      // Search the subsite make file for duplicates.
      foreach ($searches as $search => $subject) {
        // Print result subject.
        echo self::$color['cyan'] . 'Platform ' . $subject . ' found: ';
        // Perform search.
        if (isset($siteMake[$search])) {
          foreach ($siteMake[$search] as $name => $contents) {
            if (isset($profile[$search][$name]) && !isset($siteMake[$search][$name]['patch'])) {
              $duplicates[$search][] = $name;
            }
          }
        }
        // Print result.
        if (empty($duplicates[$search])) {
          echo self::$color['green'] . "none found.\r\n";
        } else {
          echo self::$color['yellow'] . implode(', ', $duplicates[$search]) . ".\r\n";
        }
      }
    }
    curl_close($curl);
  }

  /**
   * Print all found function names.
   *
   * @param string $filename
   *   The name of the file.
   * @param array $function_names.
   *   An array with the function names.
   * @param string $contents
   *   The contents of the file.
   */
  private function printAllFoundFunctionNames($filename, $function_names, $contents) {
    // Print the found hooks with file and line number.
    preg_match_all('~' . implode('\((.*)(?=\))\)|', $function_names) . '\((.*)(?=\))\)~', $contents, $matches, PREG_OFFSET_CAPTURE);
    foreach ($matches[0] as $key => $match) {
      list($before) = str_split($contents, $match[1]);
      $line_number = strlen($before) - strlen(str_replace("\n", "", $before)) + 1;
      echo self::$color['nocolor'] . "\r\n  ./" . $filename . '.install:' . $line_number . ':' . $match[0];
    }
  }

  /**
   * Converts array into Drupal's .info format.
   *
   * @param array $info
   *   An array or single value to put in an .info file.
   * @param array $parents
   *   Array of parent keys (internal use only).
   */
  private function transformArrayIntoInfoFormat($info, $parents = array()) {
    $output = '';
    if (is_array($info)) {
      foreach ($info as $k => $v) {
        $child = $parents;
        $child[] = $k;
        $output .= $this->transformArrayIntoInfoFormat($v, $child);
      }
    }
    else if (!empty($info) && count($parents)) {
      $line = array_shift($parents);
      foreach ($parents as $key) {
        $line .= is_numeric($key) ? "[]" : "[{$key}]";
      }
      $line .=  " = {$info}\n";
      return $line;
    }
    return $output;
  }

  /**
   * Parses data in Drupal's .info format.
   *
   * @param string $data
   *   The contents of the file.
   */
  public function drupalParseInfoFormat($data) {
    $info = array();

    if (preg_match_all('@^\s*((?:[^=;\[\]]|\[[^\[\]]*\])+?)\s*=\s*(?:("(?:[^"]|(?<=\\\\)")*")|(\'(?:[^\']|(?<=\\\\)\')*\')|([^\r\n]*?))\s*$@msx', $data, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        // Fetch the key and value string.
        $i = 0;
        foreach (array('key', 'value1', 'value2', 'value3') as $var) {
          $$var = isset($match[++$i]) ? $match[$i] : '';
        }
        $value = stripslashes(substr($value1, 1, -1)) . stripslashes(substr($value2, 1, -1)) . $value3;

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
