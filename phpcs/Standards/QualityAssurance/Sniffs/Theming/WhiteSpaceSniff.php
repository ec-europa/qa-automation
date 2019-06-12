<?php
/**
 * QualityAssurance_Sniffs_Theming_WhiteSpaceSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Add our remove spaces to improve reading template files.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Theming_WhiteSpaceSniff implements PHP_CodeSniffer_Sniff
{
  /**
   * Returns an array of tokens this test wants to listen for.
   *
   * @return array
   */
  public function register()
  {
    return array(
        T_CLOSE_TAG,
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
    
    // Get our tokens.
    $tokens = $phpcsFile->getTokens();
    $token  = $tokens[$stackPtr];
    
    // Find PHP close tag.    
    $tagClose = $phpcsFile->findNext(T_CLOSE_TAG, $stackPtr, null);
    $beforeClosePhp = $tokens[$tagClose - 1];
    // Get element before whitespace.
    $tagBeforeWhtsp = $phpcsFile->findPrevious(T_WHITESPACE, $tagClose - 1, null, TRUE);

    // Check for missing whitespace before closing PHP tag.
    if ($beforeClosePhp['type'] !== 'T_WHITESPACE') {
        $error = "Missing white space before closing php tag.";
        $phpcsFile->addError($error, $stackPtr, 'MissingWhiteSpace', TRUE);
    }
    // Check for existent whitespace lenght.
    if ($beforeClosePhp['type'] === 'T_WHITESPACE' &&
        $beforeClosePhp['line'] === $tokens[$tagBeforeWhtsp]['line'] &&
        $beforeClosePhp['length'] > 1) {
        $error = "There should be just one white space before the closing php tag.";
        $phpcsFile->addError($error, $stackPtr, 'MultipleWhiteSpaces', TRUE);            
    }

    // Check if element 2 positions before closing PHP tag is a colon.
    $beforeColon = $tokens[$tagClose - 2];
    if ($beforeColon['type'] === 'T_COLON') {
      $nonSpace = $tokens[$tagClose - 3];
      // Check if element 3 positions before closing PHP tag is a whitespace.
      if ($nonSpace['type'] === 'T_WHITESPACE') {
        $error = "There shouldn't be any whitespace found before the colon.";
        $phpcsFile->addError($error, $stackPtr, 'WhiteSpaceFound', TRUE);
      }
    }

  }//end process()

}//end class
