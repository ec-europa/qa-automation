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
      T_CONSTANT_ENCAPSED_STRING,
      T_LNUMBER,
      T_VARIABLE,
      T_ARRAY,
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
    // @todo check whole file first and take a coffee
    
    // Get our tokens.
    $tokens = $phpcsFile->getTokens();
    $token  = $tokens[$stackPtr];

    // Image regular expression.
    $regexp = 'sites\/[a-zA-Z]+\/(modules|themes|libraries)';

    // If image is found.
    if(preg_match("/$regexp/", $token['content'], $matches)) {
      $error = 'Hardcoded paths to modules or themes are not allowed. Please use drupal_get_path() function instead.';
      $phpcsFile->addError($error, $stackPtr);
    }

  }//end process()

}//end class
