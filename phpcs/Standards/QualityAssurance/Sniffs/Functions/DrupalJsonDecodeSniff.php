<?php
/**
 * QualityAssurance_Sniffs_Functions_DrupalJsonDecodeSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Allow json_decode in a Drupal context if 2nd parameter == FALSE.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Functions_DrupalJsonDecodeSniff implements PHP_CodeSniffer_Sniff
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
      T_FALSE,
      T_TRUE
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
    // Check whole project for string "json_decode".
    $file_content = file_get_contents($phpcsFile->getFilename());
    if (strpos($file_content, 'json_decode') === false) {
      $end = (count($phpcsFile->getTokens()) + 1);
      return $end;
    }

    // Get our tokens.
    $tokens = $phpcsFile->getTokens();
    $token  = $tokens[$stackPtr];

    // json_decode is only accepted with 2nd parameter as FALSE.
    if (
      $token['content'] == 'json_decode' &&
      $tokens[$stackPtr + 5]['type'] != 'T_FALSE'
    ) {
      $error = 'The function json_decode() is not allowed, use drupal_json_decode() instead.';
      $phpcsFile->addError($error, $stackPtr, 'HardcodedImage');
    }

    // drupal_json_encode accept no 2nd parameter.
    if (
      $token['content'] == 'drupal_json_decode' &&
      (
        $tokens[$stackPtr + 5]['type'] == 'T_FALSE' ||
        $tokens[$stackPtr + 5]['type'] == 'T_TRUE'
      )
    ) {
      $error = 'The function drupal_json_decode() have no 2nd parameter.';
      $phpcsFile->addError($error, $stackPtr, 'HardcodedImage');
    }

  }//end process()

}//end class
