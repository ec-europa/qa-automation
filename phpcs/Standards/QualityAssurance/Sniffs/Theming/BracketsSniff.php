<?php
/**
 * QualityAssurance_Sniffs_Theming_BracketsSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Use php´s alternative syntax for control structures.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Theming_BracketsSniff implements PHP_CodeSniffer_Sniff
{
  /**
   * Returns an array of tokens this test wants to listen for.
   *
   * @return array
   */
  public function register()
  {
    return array(
      T_OPEN_CURLY_BRACKET,
      T_CLOSE_CURLY_BRACKET,
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

    // Only run this sniff once per template file.
    $end = (count($phpcsFile->getTokens()) + 1);
    // If the file extension is diferent from .tpl.php return.
    $fileName = $phpcsFile->getFilename();
    $fileExtension = strtolower(substr($fileName, -8));
    if ($fileExtension !== '.tpl.php') {
      return $end;
    }
    // Check whole project for open branckets "{" or close brackets "}".
    $file_content = file_get_contents($phpcsFile->getFilename());
    // If no brackets found return.
    if (strpos($file_content, '{') === FALSE ||
      strpos($file_content, '}') === FALSE) {
      return $end;
    }
    // Get our tokens.
    $tokens = $phpcsFile->getTokens();
    $token  = $tokens[$stackPtr];
    // If our token have open or close brackets, output phpcs error.
    if (strpos($token['content'], '{') !== FALSE) {
      $error = "Use alternative PHP's syntax, changing the opening brace to a colon (:).";
      $phpcsFile->addError($error, $stackPtr, 'OpenBracket');
    }
    if (strpos($token['content'], '}') !== FALSE) {
      $error = "Use alternative PHP's syntax, changing the closing brace to a semicolon (;).";
      $phpcsFile->addError($error, $stackPtr, 'CloseBracket');
    }

  }//end process()

}//end class
