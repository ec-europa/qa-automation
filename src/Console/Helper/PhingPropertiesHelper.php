<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Helper\PhingPropertiesHelper.
 */

namespace QualityAssurance\Component\Console\Helper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PhingPropertiesHelper
 * @package QualityAssurance\Component\Console\Helper
 */
class PhingPropertiesHelper
{
  /**
   * PhingPropertiesHelper constructor.
   *
   * Setup our input output interfaces.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   */
  function __construct(InputInterface $input, OutputInterface $output)
  {
    $this->input = $input;
    $this->output = $output;
  }

  /**
   * Returns absolute path to build.xml if found.
   *
   * This function takes the current working directory and searches upwards until the
   * file gets found. When the path remains the same it means we have reached the
   * root of the filesystem and we return false.
   *
   * @param string $path
   *   This is always set with getcwd(). But because of the recursiveness of the
   *   function we can not enter it in the function itself (I think).
   * @return bool|string
   *   False if we reached root without finding it. Absolute path if found.
   */
  private function findPhingBuildFile($path = '')
  {
    $path = empty($path) ? getcwd() : $path;
    $filename = 'build.xml';
    $filepath = $path . '/' . $filename;
    // If the current folder does not contain the build file, proceed.
    if (!is_file($filepath)) {
      // If we haven't reached root yet, retry in parent folder.
      if (dirname($path) != $path) {
        return $this->findPhingBuildFile(dirname($path));
      }
      else {
        throw new \Symfony\Component\Debug\Exception\FatalErrorException(
          "Reached filesystem root without finding '$filename'.", 0, 1, __FILE__, __LINE__
        );
      }
    }
    // If found return absolute path.
    else {
      $this->output->writeln('<comment>Succesfully loaded: </comment><info>' . $filepath . '</info>');
      return "$path/build.xml";
    }
  }

  /**
   * Helper function to parse a build properties file into a flat array.
   *
   * @param string $filepath
   *   Path to the build properties file.
   * @return array
   *   Flat array with the settings non resolved.
   * @throws IOException
   */
  private function parseFile($filepath)
  {
    if (($lines = @file($filepath, FILE_IGNORE_NEW_LINES)) === false) {
      throw new \BuildException("Unable to parse contents of '$filepath'.");
    }

    // concatenate lines ending with backslash
    $linesCount = count($lines);
    for ($i = 0; $i < $linesCount; $i++) {
      if (substr($lines[$i], -1, 1) === '\\') {
        $lines[$i + 1] = substr($lines[$i], 0, -1) . ltrim($lines[$i + 1]);
        $lines[$i] = '';
      }
    }

    $properties = array();
    foreach ($lines as $line) {
      // strip comments and leading/trailing spaces
      $line = trim(preg_replace("/\s+[;#]\s.+$/", "", $line));

      if (empty($line) || $line[0] == ';' || $line[0] == '#') {
        continue;
      }

      $pos = strpos($line, '=');
      $property = trim(substr($line, 0, $pos));
      $value = trim(substr($line, $pos + 1));
      $properties[$property] = $this->inVal($value);

    } // for each line

    return $properties;
  }

  /**
   * Helper to process specific values when being read in from properties file.
   *
   * @param string $val
   *   Trimmed value.
   * @return mixed
   *   The new property value (may be boolean, etc.)
   */
  private function inVal($val)
  {
    if ($val === "true") {
      $val = true;
    } elseif ($val === "false") {
      $val = false;
    }
    return $val;
  }

  /**
   * Helper function to resolve all variable references in the properties array.
   *
   * @param array $properties
   */
  private function resolveProperties(&$properties)
  {
    foreach ($properties as $key => $value) {
      if (preg_match_all('/\$\{([^\$}]+)\}/', $value, $matches)) {
        if (!empty($matches)) {
          foreach ($matches[0] as $subkey => $match) {
            if (isset($properties[$matches[1][$subkey]])) {
              $properties[$key] = preg_replace("~" . preg_quote($match, "~") . "~", $properties[$matches[1][$subkey]], $properties[$key]);
              if (preg_match_all('/\$\{([^\$}]+)\}/', $properties[$key], $submatches)) {
                $this->resolveProperties($properties);
              }
            }
          }
        }
      }
    }
  }

  /**
   * Helper function to extract the build properties files from the build xml files.
   *
   * @param string $contents
   *   The contents of the build xml
   * @param string $buildproperties
   *   The (relative?) path to the build properties file.
   */
  private function setBuildProperties($contents, &$buildproperties) {
    if ($xml = simplexml_load_string($contents)) {
      $json = json_encode($xml);
      $array = json_decode($json, TRUE);
      if (isset($array['property'])) {
        foreach ($array['property'] as $property) {
          if (isset($property['@attributes']['file'])) {
            $buildproperties[] = $property['@attributes']['file'];
          }
        }
      }
    }
  }

  /**
   * Extract properties files from all build xml files.
   *
   * @param $buildfile
   *   Absolute path to the main build file (build.xml).
   */
  private function getAllSettings($buildfile = '') {
    $buildfile = $this->findPhingBuildFile();
    if ($buildfile) {
      $root = dirname($buildfile);
      $settings = array('project.basedir' => dirname($buildfile));
      // Array that will gather the build.properties files.
      $buildproperties = array();
      // Start by parsing the main build file.
      $contents = file_get_contents($buildfile);
      // Gather build properties from within found files.
      $this->setBuildProperties($contents, $buildproperties);

      // This also needs to be recursified.
      if (isset($buildproperties['import'])) {
        foreach($buildproperties['import'] as $import) {
          if (isset($import['@attributes']['file'])) {
            $contents = file_get_contents($root . '/' . $import['@attributes']['file']);
            $this->setBuildProperties($contents, $buildproperties);
          }
        }
      }

      foreach ($buildproperties as $propertiesfile) {
        if (is_file($root . '/' . $propertiesfile)) {
          $settings += $this->parsefile($root . '/' . $propertiesfile);

        }
      }
    }
    $this->resolveProperties($settings);
    return $settings;
  }
  
  /**
   * Public helper function to request required settings.
   *
   * @param array $options
   *   An array of properties.
   * @return array
   *   Returns a selection of properties in an array.
   * @throws \Symfony\Component\Debug\Exception\FatalErrorException
   */
  public function requestRequiredSettings($options) {
    // Remove standard options.
    $options = array_diff_key($options, array_flip(array(
      'help',
      'quiet',
      'verbose',
      'version',
      'ansi',
      'no-ansi',
      'no-interaction'
    )));
    $settings = $this->getAllSettings();
    $selection = array();
    foreach ($options as $option => $value) {
      if (isset($settings[$option])) {
        $selection[$option] = $settings[$option];
      }
      else {
        throw new \Symfony\Component\Debug\Exception\FatalErrorException(
          "Required property ' . $option . ' not provided.", 0, 1, __FILE__, __LINE__
        );
      }
    }
    return $selection;
  }

  /**
   * Public function to request specific settings.
   *
   * @param $options
   *   An array of properties to select valued by the property name.
   * @return array
   *   An array of selected properties where the property name has been replaced by
   *   the property value.
   * @throws \Symfony\Component\Debug\Exception\FatalErrorException
   */
  public function requestSettings($options) {

    $settings = $this->getAllSettings();
    $selection = array();
    foreach ($options as $key => $value) {
      if (isset($settings[$value])) {
        $selection[$key] = $settings[$value];
      }
      else {
        throw new \Symfony\Component\Debug\Exception\FatalErrorException(
          "Requested property ' . $value . ' not found.", 0, 1, __FILE__, __LINE__
        );
      }
    }
    return $selection;
  }

  /**
   *
   * Find the relative file system path between two file system paths
   *
   * @param string $frompath
   *   Path to start from
   * @param string $topath
   *   Path we want to end up in
   *
   * @return string
   *   Path leading from $frompath to $topath
   */
  public function findRelativePath ( $frompath, $topath ) {
    $from = explode( DIRECTORY_SEPARATOR, $frompath ); // Folders/File
    $to = explode( DIRECTORY_SEPARATOR, $topath ); // Folders/File
    $relpath = '';

    $i = 0;
    // Find how far the path is the same
    while ( isset($from[$i]) && isset($to[$i]) ) {
      if ( $from[$i] != $to[$i] ) break;
      $i++;
    }
    $j = count( $from ) - 1;
    // Add '..' until the path is the same
    while ( $i <= $j ) {
      if ( !empty($from[$j]) ) $relpath .= '..'.DIRECTORY_SEPARATOR;
      $j--;
    }
    // Go to folder from where it starts differing
    while ( isset($to[$i]) ) {
      if ( !empty($to[$i]) ) $relpath .= $to[$i].DIRECTORY_SEPARATOR;
      $i++;
    }

    // Strip last separator
    return substr($relpath, 0, -1);
  }
}
