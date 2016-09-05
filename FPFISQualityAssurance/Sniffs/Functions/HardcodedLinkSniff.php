<?php
/**
 * FPFISQualityAssurance_Sniffs_Functions_HardcodedLinkSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Hardcoded links have to be replaced by l() function.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class FPFISQualityAssurance_Sniffs_Functions_HardcodedLinkSniff implements PHP_CodeSniffer_Sniff
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
        // Get our tokens.
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];
        // Link regular expression.
        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        // If link is found.
        if(preg_match("/$regexp/siU", $token['content'], $matches)) {
            $error = 'Hardcoded link not allowed, use l() function instead.';
            $phpcsFile->addError($error, $stackPtr, 'HardcodedLink');
        }
    }//end process()


}//end class
