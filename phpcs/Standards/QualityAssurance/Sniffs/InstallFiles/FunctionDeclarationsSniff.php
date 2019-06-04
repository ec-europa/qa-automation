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
class QualityAssurance_Sniffs_InstallFiles_FunctionDeclarationsSniff implements PHP_CodeSniffer_Sniff
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

        $allowedHooks = array(
          $fileName . '_install',
          $fileName . '_install_tasks',
          $fileName . '_uninstall',
          $fileName . '_enable',
          $fileName . '_disable',
          $fileName . '_schema',
          $fileName . '_field_schema',
          $fileName . '_requirements',
          $fileName . '_update_last_removed',
        );

        if (!in_array($functionName, $allowedHooks) && !preg_match('/' . $fileName . '_update_7\d{3}/', $functionName)) {
            $warning = 'Move the "%s" function declaration in to a file named %s.install.inc and include that file in %s.install.';
            $phpcsFile->addError($warning, $stackPtr, 'NonHookFound', array($functionName, $fileName, $fileName));
        }
    }//end process()
}//end class
