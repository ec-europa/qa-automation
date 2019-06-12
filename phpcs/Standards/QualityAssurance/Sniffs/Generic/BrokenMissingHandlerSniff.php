<?php
/**
 * QualityAssurance_Sniffs_Generic_BrokenMissingHandlerSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Broken/missing handler on views export should be fixed.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Generic_BrokenMissingHandlerSniff implements PHP_CodeSniffer_Sniff
{
  /**
   * Returns an array of tokens this test wants to listen for.
   *
   * @return array
   */
    public function register()
    {
        return array(
        T_COMMENT,
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
        // Only run this sniff once per strongarm exported file.
        $end = (count($phpcsFile->getTokens()) + 1);

        // If the file extension is diferent from views_default.inc return.
        $fileName = $phpcsFile->getFilename();
        $fileExtension = strtolower(substr($fileName, -17));
        if ($fileExtension !== 'views_default.inc') {
            return $end;
        }

        // Check whole project for string "Broken/missing handler".
        $file_content = file_get_contents($phpcsFile->getFilename());
        // If no string 'Broken/missing handler' found return.
        if (strpos($file_content, 'Broken/missing handler') === false) {
            return $end;
        }

        // Get our tokens.
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];

        // If our token have the keyword 'Broken/missing handler', output phpcs error.
        if (strpos($token['content'], 'Broken/missing handler') !== false) {
            $error = "Broken/missing handler on a views export is not allowed.";
            $phpcsFile->addError($error, $stackPtr, 'Views');
        }
    }//end process()
}//end class
