<?php
/**
 * QualityAssurance_Sniffs_Fields_DatestampSniff.
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
class QualityAssurance_Sniffs_FeaturesFiles_DatestampFieldsSniff implements PHP_CodeSniffer_Sniff
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
          T_LNUMBER,
          T_VARIABLE,
          T_ARRAY,
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

        // Get our tokens.
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];
        // Find the variable.
        if ($token['content'] == '$field_bases') {
            // Find the field name.
            if ($fieldName = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($stackPtr + 1), ($stackPtr + 3), false)) {
                // Set array variables.
                $fieldBaseArray = $phpcsFile->findNext(T_ARRAY, ($fieldName + 1), ($fieldName + 6), false);
                $fieldBaseArrayStart = $tokens[$fieldBaseArray]['parenthesis_opener'];
                $fieldBaseArrayEnd = $tokens[$fieldBaseArray]['parenthesis_closer'];
                // Find the type property.
                if ($type = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, $fieldBaseArrayStart, $fieldBaseArrayEnd, false, "'type'")) {
                    // If field type is not datestamp.
                    if (($isDatestamp = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($type + 1), ($type + 5), false))
                      && ($tokens[$isDatestamp]['content'] === "'datetime'" || $tokens[$isDatestamp]['content'] === "'date'")) {
                        // Set error.
                        $error = 'Field ' . $tokens[$fieldName]['content'] . ' is of type ' . $tokens[$isDatestamp]['content'] . '; needs to be \'datestamp\'';
                        $fix = $phpcsFile->addFixableError($error, $isDatestamp, 'DatestampFields');
                        if ($fix === true) {
                            $phpcsFile->fixer->beginChangeset();
                            $phpcsFile->fixer->replaceToken($isDatestamp, "'datestamp'");
                            $phpcsFile->fixer->endChangeset();
                        }
                    }
                }
            }
        }

    }//end process()


}//end class
