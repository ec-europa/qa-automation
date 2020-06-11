<?php
/**
 * QualityAssurance_Sniffs_FeaturesFiles_ForbiddenPermissionsSniff.
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
class QualityAssurance_Sniffs_FeaturesFiles_ForbiddenPermissionsSniff implements PHP_CodeSniffer_Sniff
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
        $fileExtension = strtolower(substr($phpcsFile->getFilename(), -29));
        if ($fileExtension !== '.features.user_permission.inc') {
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

        $arrayStart = $tokens[$stackPtr][$parenthesis_opener];
        $arrayEnd = $tokens[$stackPtr][$parenthesis_closer];

        // Loop over array tokens.
        while ($arrayStart = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($arrayStart + 1), $arrayEnd)) {
            // Find the field_name key.
            if ($tokens[$arrayStart]['content'] === "'name'") {
                $permissionName = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($arrayStart + 1), ($arrayStart + 5));
                $riskyPermissions = array(
                    'administer modules',
                    'administer software updates',
                    'administer features',
                    'manage features',
                    'administer ckeditor_lite',
                    'administer jquery update',
                    'access devel information',
                    'execute php code',
                    'manage feature nexteuropa_dgt_connector',
                    // MULTISITE-23799 - Risky permissions update.
                    'access all views',
                    'administer content types',
                    'administer entityform types',
                    'administer fields',
                    'administer file types',
                    'administer filters',
                    'administer om maximenu',
                    'administer page manager',
                    'administer permissions',
                    'administer site configuration',
                    'administer themes',
                    'administer users',
                    'administer views',
                    'bypass file access',
                    'bypass node access',
                    'bypass rules access',
                    'generate features',
                    'rename features',
                    'use page manager',
                    'use PHP for label patterns',
                    'administer ecas'
                );
                // If it's a risky permission, trow an error.
                if (in_array(str_replace("'", '', $tokens[$permissionName]['content']), $riskyPermissions)) {
                    $error = 'The use of permission ' . str_replace("'", '"', $tokens[$permissionName]['content']) . ' is forbidden.';
                    $phpcsFile->addError($error, $permissionName, 'Permissions');
                }
                // Exit array.
                return $arrayEnd;
            }
        }

        // If we have checked level one, exit the array.
        return $arrayEnd;
    }//end process()
}//end class
