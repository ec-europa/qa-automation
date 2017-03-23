<?php
/**
 * QualityAssurance_Sniffs_Generic_EmptySettingsSniff.
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
class QualityAssurance_Sniffs_Generic_EmptySettingsSniff implements PHP_CodeSniffer_Sniff
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
    // Only run this sniff once per strongarm exported file file.
    $end = (count($phpcsFile->getTokens()) + 1);

    // If the file extension is diferent from strongarm.inc return.
    $fileName = $phpcsFile->getFilename();
    $fileExtension = strtolower(substr($fileName, -13));
    if ($fileExtension !== 'strongarm.inc') {
      return $end;
    }

    // Check whole project for string "sites/".
    $file_content = file_get_contents($phpcsFile->getFilename());
    // If no pathauto setting found return.
    if (strpos($file_content, 'pathauto') === false) {
      return $end;
    }

    // Get our tokens.
    $tokens = $phpcsFile->getTokens();
    $token  = $tokens[$stackPtr];

    // If our token have the keyword pathauto, check next property and value.
    if (strpos($token['content'], 'pathauto') !== false) {
        if (
          $tokens[$stackPtr + 10]['content'] == "''" &&
          $tokens[$stackPtr + 6]['content'] == 'value'
        ) {

          $warning = "Empty strongarm settings for " . $token['content'] . " are not allowed.";
          $phpcsFile->addWarning($warning, $stackPtr, 'EmptySettings');
        }
    }

  }//end process()

}//end class
