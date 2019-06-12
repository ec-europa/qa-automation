<?php
/**
 * QualityAssurance_Sniffs_Generic_HardcodedPathSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Hardcoded paths to theme or modules need to be removed or replace by a call
 * to drupal_get_path() function.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Generic_HardcodedPathSniff implements PHP_CodeSniffer_Sniff
{
  /**
   * Returns an array of tokens this test wants to listen for.
   *
   * @return array
   */
    public function register()
    {
        return array(
        T_STRING,
        T_CONSTANT_ENCAPSED_STRING,
        T_INLINE_HTML
        );
    }//end register()


  /**
   * Processes this test, when one of its tokens is encountered.
   *
   * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
   * @param int                  $stackPtr  The position of the current token in the
   *                                        stack passed in $tokens.
   *
   * @return void
   */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // Check whole project for string "sites/".
        $file_content = file_get_contents($phpcsFile->getFilename());

        if (strpos($file_content, 'sites') === false) {
            $end = (count($phpcsFile->getTokens()) + 1);
            return $end;
        }

        // Get our tokens.
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];

        // Path regular expression.
        $regexp = 'sites/[^/]+/(files|libraries|modules|themes)';

        // If hardcoded path is found.
        if (preg_match("~$regexp~", $token['content'], $matches)) {
            $error = "Internal hardcoded paths are not allowed. ";
            switch ($matches[1]) {
                case 'modules':
                    $error .= "Please use drupal_get_path('module', \$name).";
                    break;
                case 'themes':
                    $error .= "Please use drupal_get_path('theme', \$name).";
                    break;
                case 'libraries':
                    $error .= "Please use libraries_get_path(\$name).";
                    break;
                case 'files':
                    $error .= "Please use variable_get('file_public|private|temporary_path').";
                    break;
            }
            $phpcsFile->addError($error, $stackPtr, 'HardcodedPath');
        }
    }//end process()
}//end class
