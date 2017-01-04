<?php
/**
 * QualityAssurance_Sniffs_FeaturesFiles_FieldNameLengthSniff.
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
class QualityAssurance_Sniffs_FeaturesFiles_FieldNameLengthSniff implements PHP_CodeSniffer_Sniff
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
                if ($type = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, $fieldBaseArrayStart, $fieldBaseArrayEnd, false, "'field_name'")) {
                    // If field type is not datestamp.
                    if ($fieldMachineName = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($type + 1), ($type + 5), false)) {
                        // Set error.
                        $field_name = str_replace('\'', '', $tokens[$fieldMachineName]['content']);
                        $length = strlen($field_name);
                        if ($length > 32) {
                            $error = 'Machine name "' . $field_name . '" has ' . $length . ' characters and may not be longer than 32 characters.';
                            $phpcsFile->addError($error, $fieldMachineName, 'TooLong');
                        }
                    }
                }
            }
        }

    }//end process()


}//end class
