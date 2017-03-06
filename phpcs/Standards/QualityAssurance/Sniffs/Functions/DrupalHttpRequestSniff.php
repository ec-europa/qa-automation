<?php
/**
 * QualityAssurance_Sniffs_Functions_DrupalHttpRequestSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Enforce the use of drupal_http_request().
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Functions_DrupalHttpRequestSniff extends Generic_Sniffs_PHP_ForbiddenFunctionsSniff
{

    /**
     * A list of forbidden functions with their alternatives.
     *
     * The value is NULL if no alternative exists, i.e., the function should
     * just not be used.
     *
     * @var array(string => string|null)
     */
    public $forbiddenFunctions = array(
        'curl_init' => 'drupal_http_request',
    );

    /**
     * If true, an error will be thrown; otherwise a warning.
     *
     * @var bool
     */
    public $error = true;

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $ignore = array(
          T_DOUBLE_COLON    => true,
          T_OBJECT_OPERATOR => true,
          T_FUNCTION        => true,
          T_CONST           => true,
          T_PUBLIC          => true,
          T_PRIVATE         => true,
          T_PROTECTED       => true,
          T_AS              => true,
          T_NEW             => true,
          T_INSTEADOF       => true,
          T_NS_SEPARATOR    => true,
          T_IMPLEMENTS      => true,
        );

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);

        // If function call is directly preceded by a NS_SEPARATOR it points to the
        // global namespace, so we should still catch it.
        if ($tokens[$prevToken]['code'] === T_NS_SEPARATOR) {
            $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($prevToken - 1), null, true);
            if ($tokens[$prevToken]['code'] === T_STRING) {
                // Not in the global namespace.
                return;
            }
        }

        if (isset($ignore[$tokens[$prevToken]['code']]) === true) {
            // Not a call to a PHP function.
            return;
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if (isset($ignore[$tokens[$nextToken]['code']]) === true) {
            // Not a call to a PHP function.
            return;
        }

        if ($tokens[$stackPtr]['code'] === T_STRING && $tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
            // Not a call to a PHP function.
            return;
        }

        $function = strtolower($tokens[$stackPtr]['content']);
        $pattern  = null;

        if ($this->patternMatch === true) {
            $count   = 0;
            $pattern = preg_replace(
              $this->forbiddenFunctionNames,
              $this->forbiddenFunctionNames,
              $function,
              1,
              $count
            );

            if ($count === 0) {
                return;
            }

            // Remove the pattern delimiters and modifier.
            $pattern = substr($pattern, 1, -2);
        } else {
            if (in_array($function, $this->forbiddenFunctionNames) === false) {
                return;
            }
        }//end if

        $this->addError($phpcsFile, $stackPtr, $function, $pattern);

    }//end process()


    /**
     * Generates the error or warning for this sniff.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the forbidden function
     *                                        in the token array.
     * @param string               $function  The name of the forbidden function.
     * @param string               $pattern   The pattern used for the match.
     *
     * @return void
     */
    protected function addError($phpcsFile, $stackPtr, $function, $pattern=null)
    {
        $data  = array($function);
        $error = 'The use of function %s() is ';
        if ($this->error === true) {
            $type   = 'Found';
            $error .= 'forbidden';
        }

        if ($pattern === null) {
            $pattern = $function;
        }

        if ($this->forbiddenFunctions[$pattern] !== null
          && $this->forbiddenFunctions[$pattern] !== 'null'
        ) {
            $type  .= 'WithAlternative';
            $data[] = $this->forbiddenFunctions[$pattern];
            $error .= '; use %s() instead. Refer to the "chr" module that replaces the drupal_http_request() callback in the FPFIS platform.';

            $phpcsFile->addError($error, $stackPtr, $type, $data);
        }

    }//end addError()

}//end class
