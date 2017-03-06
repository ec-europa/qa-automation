<?php
/**
 * QualityAssurance_Sniffs_FeaturesFiles_LockedFieldsSniff.
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
class QualityAssurance_Sniffs_FeaturesFiles_LockedFieldsSniff implements PHP_CodeSniffer_Sniff
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
        $fileExtension = strtolower(substr($phpcsFile->getFilename(), -24));
        if ($fileExtension !== '.features.field_base.inc') {
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
            // Find the locked key.
            if ($tokens[$arrayStart]['content'] === "'locked'") {
                // Check if the field is unlocked.
                if ($unlocked = $phpcsFile->findNext(T_LNUMBER, ($arrayStart + 1), ($arrayStart + 5), false, "0")) {
                    // Set error.
                    $error = 'Unlocked field detected.';
                    $fix = $phpcsFile->addFixableError($error, $unlocked, 'UnlockedFields');
                    if ($fix === true) {
                        $phpcsFile->fixer->beginChangeset();
                        $phpcsFile->fixer->replaceToken($unlocked, '1');
                        $phpcsFile->fixer->endChangeset();
                    }
                }
                // Exit the array.
                return $arrayEnd;
            }
        }

        // If we have checked level one, exit the array.
        return $arrayEnd;

    }//end process()


}//end class
