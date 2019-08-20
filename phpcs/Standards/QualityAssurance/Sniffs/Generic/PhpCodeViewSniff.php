<?php
/**
 * QualityAssurance_Sniffs_Generic_PhpCodeViewSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Including PHP code on views is not allowed.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Generic_PhpCodeViewSniff implements PHP_CodeSniffer_Sniff
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
        // Only run this sniff once per views exported file.
        $end = (count($phpcsFile->getTokens()) + 1);

        // If the file extension is diferent from views_default.inc return.
        $fileName = $phpcsFile->getFilename();
        $fileExtension = strtolower(substr($fileName, -17));
        if ($fileExtension !== 'views_default.inc') {
            return $end;
        }

        // Check views files for PHP code.
        $file_content = file_get_contents($phpcsFile->getFilename());
        // Get our tokens.
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];

        if ((strpos($file_content, 'php') === false && $token['line'] > 1) &&
        strpos($file_content, 'code') === false) {
            return $end;
        }

        if ($token['content'] === "'default_argument_type'") {
            $php = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, $stackPtr + 1);
            if ($tokens[$php]['content'] === "'php'") {
                $error = "PHP code is not allowed on views export.";
                $phpcsFile->addError($error, $stackPtr, 'Views');
            }
        }
    }//end process()
}//end class
