<?php

/**
 * QualityAssurance_Sniffs_Functions_DrupalJsonDecodeSniff.
 *
 * Allow json_decode in a Drupal context if 2nd parameter == FALSE.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Functions_DrupalJsonDecodeSniff extends Drupal_Sniffs_Semantics_FunctionCall
{

    /**
     * We also want to catch $this->t() calls in Drupal 8.
     *
     * @var bool
     */
    protected $includeMethodCalls = true;

    /**
     * Returns an array of function names this test wants to listen for.
     *
     * @return array
     */
    public function registerFunctionNames()
    {
        return array(
            'json_decode',
            'drupal_json_decode',
        );

    }//end registerFunctionNames()

    /**
     * Returns start and end token for a given argument number.
     *
     * @param int $number
     * Indicates which argument should be examined, starting with
     * 1 for the first argument.
     *
     * @return array(string => int)
     */

    /**
     * Processes this function call.
     *
     * @param PHP_CodeSniffer_File $phpcsFile    The file being scanned.
     * @param int                  $stackPtr     The position of the function call in
     *                                           the stack.
     * @param int                  $openBracket  The position of the opening
     *                                           parenthesis in the stack.
     * @param int                  $closeBracket The position of the closing
     *                                           parenthesis in the stack.
     *
     * @return void
     */
    public function processFunctionCall(
        PHP_CodeSniffer_File $phpcsFile,
        $stackPtr,
        $openBracket,
        $closeBracket
    ) {
        $tokens   = $phpcsFile->getTokens();
        $argument = $this->getArgument(2);

        if ($argument != false) {
            // Function drupal_json_decode with 2nd parameter is error.
            if (
                $tokens[$stackPtr]['content'] == 'drupal_json_decode' &&
                $argument != false
            ) {
                $error = 'The function drupal_json_decode() does not take a 2nd parameter.';
                $phpcsFile->addError($error, $stackPtr, 'JSONDecode');
            }

            // Function json_encode only can have T_FALSE as second parameter.
            if (
                $tokens[$stackPtr]['content'] == 'json_decode' &&
                $tokens[$argument['start']]['type'] != 'T_FALSE'
            ) {
                $error = 'The function json_decode() is not allowed, use drupal_json_decode() instead.';
                $phpcsFile->addError($error, $stackPtr, 'JSONDecode');
            }
        }
        else {
            if ($tokens[$stackPtr]['content'] == 'json_decode') {
                $error = 'The function json_decode() is not allowed, use drupal_json_decode() instead.';
                $phpcsFile->addError($error, $stackPtr, 'JSONDecode');
            }
        }
    }//end process()

}//end class
