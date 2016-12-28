<?php
namespace QualityAssurance\Component\Console\Helper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrupalInfoFormatHelper
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
