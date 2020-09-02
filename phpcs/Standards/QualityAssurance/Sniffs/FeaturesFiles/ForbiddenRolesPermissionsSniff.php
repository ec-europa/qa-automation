<?php
/**
 * QualityAssurance_Sniffs_FeaturesFiles_ForbiddenRolesPermissionsSniff.
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
class QualityAssurance_Sniffs_FeaturesFiles_ForbiddenRolesPermissionsSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_ARRAY,
            T_OPEN_SHORT_ARRAY,
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
        $fileExtension = strtolower(substr($phpcsFile->getFilename(), -31));
        if ($fileExtension !== '.features.roles_permissions.inc') {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        // Support long and short syntax.
        $parenthesis_opener = 'parenthesis_opener';
        $parenthesis_closer = 'parenthesis_closer';
        if ($tokens[$stackPtr]['code'] === T_OPEN_SHORT_ARRAY) {
            $parenthesis_opener = 'bracket_opener';
            $parenthesis_closer = 'bracket_closer';
        }

        $lastItem = $phpcsFile->findPrevious(
            PHP_CodeSniffer_Tokens::$emptyTokens,
            ($tokens[$stackPtr][$parenthesis_closer] - 1),
            $stackPtr,
            true
        );

        // Empty array.
        if ($lastItem === $tokens[$stackPtr][$parenthesis_opener]) {
            return;
        }

        // Inline array.
        if ($tokens[$tokens[$stackPtr][$parenthesis_opener]]['line'] === $tokens[$tokens[$stackPtr][$parenthesis_closer]]['line']) {
            return;
        }
        $roleStart = $tokens[$stackPtr][$parenthesis_opener];
        $roleEnd   = $tokens[$stackPtr][$parenthesis_closer];

        $arrayPermissions = $phpcsFile->findNext(T_ARRAY, ($roleStart + 1));
        $arrayStart = $tokens[$arrayPermissions][$parenthesis_opener];

        // Loop over array tokens.
        while ($arrayStart = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($arrayStart + 1), $roleEnd)) {
            // Find the field_name key.
            $riskyPermissions = array(
                'access all views',
                'access devel information',
                'administer ckeditor_lite',
                'administer content types',
                'administer ecas',
                'administer entityform types',
                'administer features',
                'administer fields',
                'administer file types',
                'administer filters',
                'administer jquery update',
                'administer modules',
                'administer om maximenu',
                'administer page manager',
                'administer permissions',
                'administer site configuration',
                'administer software updates',
                'administer themes',
                'administer users',
                'administer views',
                'bypass file access',
                'bypass node access',
                'bypass rules access',
                'execute php code',
                'generate features',
                'manage feature nexteuropa_dgt_connector',
                'manage features',
                'rename features',
                'use page manager',
                'use PHP for label patterns',
            );
            // If it's a risky permission, trow an error.
            if (in_array(str_replace("'", '', $tokens[$arrayStart]['content']), $riskyPermissions)) {
                $error = 'The use of permission ' . str_replace("'", '"', $tokens[$arrayStart]['content']) . ' is forbidden.';
                $phpcsFile->addError($error, $arrayStart, 'Permissions');
            }
        }
        return $roleEnd;
    }//end process()
}//end class
