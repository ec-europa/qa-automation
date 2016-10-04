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
    'double' => "======================================================================" . PHP_EOL,
    'single' => PHP_EOL . "----------------------------------------------------------------------",
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
    $finder->files()
          ->name('*.info')
          ->in($this->distBuildDir)
          ->exclude(array('contrib', 'contributed'))
          ->sortByName();
    // Loop over files and extract the info files.
    $i = 1;
    $options = array();
    echo self::$color['magenta'] . "     0) Select all" . PHP_EOL;
    foreach ($finder as $file) {
      $filepathname = $file->getRealPath();
      $filename = basename($filepathname);
      echo "     " . $i . ") " . $filename, PHP_EOL;
      $options[$i] = $filepathname;
      $i++;
    }
    // Stop for selection of module if autoselect is disabled.
    echo self::$color['nocolor'] . PHP_EOL;
    $selected = $options;
    if (!$this->skipSelect) {
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
   * @param array $pathinfo
   *   The pathinfo of the info file.
   */
  public function checkGitDiffUpdateHook($pathinfo) {
    // Find file in lib folder.
    $filename = $pathinfo['filename'];
    $updates = 0;
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

      // Find new hook update functions in diff.
      $regex = '~' . $filename . '_update_7\d{3}~';
      $contents = is_file($filepath) ? file_get_contents($filepath) : '';
      preg_match_all($regex, $diff, $matches);
      $updates = $matches[0];
      $count = count($updates);
    }

    // Print result.
    echo self::$color['cyan'] . PHP_EOL . "Check for new updates in branch: ";
    if (empty($updates)) {
      echo self::$color['green'] . "none found." . self::$color['nocolor'];
    }
    else {
      if ($count === 1) {
        echo self::$color['yellow'] . "1 update found.";
      }
      else {
        echo self::$color['red'] . $count . " updates found.";
        $this->passbuild = FALSE;
      }
      // Print the found hooks with file and line number.
      preg_match_all('~' . implode('|', $updates) . '~', $contents, $matches, PREG_OFFSET_CAPTURE);
      foreach ($matches[0] as $key => $match) {
        list($before) = str_split($contents, $match[1]);
        $line_number = strlen($before) - strlen(str_replace("\n", "", $before)) + 1;
        echo self::$color['nocolor'] . PHP_EOL . "  ./" . $filename . '.install:' . $line_number . ':' . $match[0];
      }
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
    echo self::$color['cyan'] . PHP_EOL . "Check for coding standard ignores: ";
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
        echo PHP_EOL . "  ." . implode(':', array_map('trim', $lines));
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
    echo self::$color['cyan'] . PHP_EOL . "Check for todo's for this release: ";
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
        echo PHP_EOL . "  ." . implode(':', array_map('trim', $lines));
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
    echo self::$color['cyan'] . PHP_EOL . "Check for coding standards: " . self::$color['nocolor'];
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
      echo self::$color['green'] . "no violations." . PHP_EOL;
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
        echo PHP_EOL . "  ." . str_replace($dirname, '', $result);
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
  public function checkGitDiffSiteMake($makefile) {
    // Find site.make in resources folder.
    $searches = array(
      'projects' => 'modules or themes',
      'libraries' => 'libraries',
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
      echo self::$color['cyan'] . 'New ' . $subject . ' found: ';
      if (empty($additions)) {
        echo self::$color['green'] . "none found." . PHP_EOL;
      }
      else {
        echo self::$color['yellow'] . implode(', ', $additions) . "." . PHP_EOL;
      }
    }
  }

  /**
   * Check modules, themes or libraries used in the platform.
   *
   * @param string $makefile
   *   The makefile to check.
   */
  public function checkSiteMakeForPlatformDependencies($makefile) {
    // Find site.make in resources folder.
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
          echo self::$color['green'] . "none found." . PHP_EOL;
        } else {
          echo self::$color['yellow'] . implode(', ', $duplicates[$search]) . "." . PHP_EOL;
        }
      }
    }
    curl_close($curl);
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
