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
class QualityAssurance_Sniffs_InstallFiles_InstallUpdateCallbacksSniff implements PHP_CodeSniffer_Sniff
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

        if ($functionName !==  $fileName . '_install') {
            return;
        }

        // Search in the function body for drupal_add_css() calls.
        $string = $phpcsFile->findNext(
            T_STRING,
            $tokens[$stackPtr]['scope_opener'],
            $tokens[$stackPtr]['scope_closer']
        );
        while ($string !== false) {
            if (preg_match('/^' . $fileName . '_update_7\d{3}$/', $tokens[$string]['content'])) {
                $opener = $phpcsFile->findNext(
                    PHP_CodeSniffer_Tokens::$emptyTokens,
                    ($string + 1),
                    null,
                    true
                );
                if ($opener !== false
                  && $tokens[$opener]['code'] === T_OPEN_PARENTHESIS
                ) {
                    $warning = 'Do not call "%s()" in hook_install().';
                    $phpcsFile->addWarning($warning, $string, 'CallbackNotAllowed', array($tokens[$string]['content']));
                }
            }

            $string = $phpcsFile->findNext(
                T_STRING,
                ($string + 1),
                $tokens[$stackPtr]['scope_closer']
            );
        }//end while
    }//end process()
}//end class
