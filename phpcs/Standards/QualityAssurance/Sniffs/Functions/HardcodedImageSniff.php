<?php
/**
 * QualityAssurance_Sniffs_Functions_HardcodedImageSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Hardcoded images in templates have to be replaced by theme('image') function.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Functions_HardcodedImageSniff implements PHP_CodeSniffer_Sniff
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
        // Only perform this check on a .tpl.php file.
        $fileExtension = strtolower(substr($phpcsFile->getFilename(), -8));
        if ($fileExtension !== '.tpl.php') {
            return;
        }

        // Get our tokens.
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];

        // Image regular expression.
        $regexp = '<img.*?src\s*=.*?>';

        // If image is found.
        if (preg_match("/$regexp/", $token['content'], $matches)) {
            $error = 'Hardcoded image not allowed in template file, use theme(\'image\') function instead.';
            $phpcsFile->addError($error, $stackPtr, 'HardcodedImage');
        }
    }//end process()
}//end class
