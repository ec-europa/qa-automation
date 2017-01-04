<?php
/**
 * Ensures hooks comment on update function is correct.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Ensures hooks comment on update function is correct.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_InstallFiles_Update7000Sniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_FUNCTION);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $fileExtension = strtolower(substr($phpcsFile->getFilename(), -7));
        if ($fileExtension !== 'install') {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $functionName = $tokens[$stackPtr +2]['content'];
        $fileName = substr(basename($phpcsFile->getFilename()), 0, -8);

        if ($functionName === $fileName . '_update_7000') {
            $phpcsFile->addError('Update schema 7000 is reserved for upgrading from D6 to D7 and will not run otherwise.', $stackPtr, "Update7000NotAllowed");
        }

    }//end process()


}//end class
