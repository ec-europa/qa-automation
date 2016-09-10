<?php
/**
 * QualityAssurance_Sniffs_Permissions_ForbiddenSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * "menu", "php" dependencies and "taxonomy tags" are forbidden in Drupal info files.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Permissions_ForbiddenSniff implements PHP_CodeSniffer_Sniff
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
          T_VARIABLE,
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
        // Only perform this check on a .features.field_base.inc file.
        $fileExtension = strtolower(substr($phpcsFile->getFilename(), -29));
        if ($fileExtension !== '.features.user_permission.inc') {
            return;
        }

        // Get our tokens.
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];
        $riskyPermissions = array(
          'administer modules',
          'administer software updates',
          'administer features',
          'manage features',
          'administer ckeditor_lite',
          'administer jquery update',
          'access devel information',
          'execute php code'
        );
        // Find the variable.
        if ($token['content'] == '$permissions') {
            // Find the permission name.
            if (($permission = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($stackPtr + 1), ($stackPtr + 3), false))
              && in_array(str_replace("'", "", $tokens[$permission]['content']), $riskyPermissions)) {
                // Set error.
                $error = 'The use of permission ' . $tokens[$permission]['content'] . ' is forbidden.';
                $fix = $phpcsFile->addError($error, $permission, 'Permissions');
            }
        }

    }//end process()


}//end class
