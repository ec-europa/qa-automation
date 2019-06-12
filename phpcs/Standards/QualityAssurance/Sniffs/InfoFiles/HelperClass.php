<?php
/**
 * QualityAssurance_Sniffs_InfoFiles_HelperClass.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Class for helper functions for our sniffs.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_InfoFiles_HelperClass
{
    /**
     * Parses a Drupal info file. Copied from Drupal core drupal_parse_info_format().
     *
     * @param string $data The contents of the info file to parse
     *
     * @return array The info array.
     */
    public static function drupalParseInfoFormat($data)
    {
        $info      = array();
        $constants = get_defined_constants();

        if (preg_match_all(
            '
          @^\s*                           # Start at the beginning of a line, ignoring leading whitespace
          ((?:
            [^=;\[\]]|                    # Key names cannot contain equal signs, semi-colons or square brackets,
            \[[^\[\]]*\]                  # unless they are balanced and not nested
          )+?)
          \s*=\s*                         # Key/value pairs are separated by equal signs (ignoring white-space)
          (?:
            ("(?:[^"]|(?<=\\\\)")*")|     # Double-quoted string, which may contain slash-escaped quotes/slashes
            (\'(?:[^\']|(?<=\\\\)\')*\')| # Single-quoted string, which may contain slash-escaped quotes/slashes
            ([^\r\n]*?)                   # Non-quoted string
          )\s*$                           # Stop at the next end of a line, ignoring trailing whitespace
          @msx',
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

                $value = stripslashes(substr($value1, 1, -1)).stripslashes(substr($value2, 1, -1)).$value3;

                // Parse array syntax.
                $keys   = preg_split('/\]?\[/', rtrim($key, ']'));
                $last   = array_pop($keys);
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
                if (isset($constants[$value])) {
                    $value = $constants[$value];
                }

                // Insert actual value.
                if ($last == '') {
                    $last = count($parent);
                }

                $parent[$last] = $value;
            }//end foreach
        }//end if

        return $info;
    }//end drupalParseInfoFormat()
}//end class
