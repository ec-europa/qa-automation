<?php
/**
 * QualityAssurance_Sniffs_Functions_DrupalWrappersSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Enforce the use of Drupal Wrappers.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Functions_DrupalWrappersSniff extends Generic_Sniffs_PHP_ForbiddenFunctionsSniff
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
        'basename' => 'drupal_basename',
        'chmod' => 'drupal_chmod',
        'dirname' => 'drupal_dirname',
        'http_build_query' => 'drupal_http_build_query',
        'json_encode' => 'drupal_json_encode',
        'mkdir' => 'drupal_mkdir',
        'move_uploaded_file' => 'drupal_move_uploaded_file',
        'parse_url' => 'drupal_parse_url',
        'realpath' => 'drupal_realpath',
        'register_shutdown_function' => 'drupal_register_shutdown_function',
        'rmdir' => 'drupal_rmdir',
        'session_regenerate' => 'drupal_session_regenerate',
        'session_start' => 'drupal_session_start',
        'set_time_limit' => 'drupal_set_time_limit',
        'strlen' => 'drupal_strlen',
        'strtolower' => 'drupal_strtolower',
        'strtoupper' => 'drupal_strtoupper',
        'substr' => 'drupal_substr',
        'tempnam' => 'drupal_tempnam',
        'ucfirst' => 'drupal_ucfirst',
        'unlink' => 'drupal_unlink',
        'xml_parser_create' => 'drupal_xml_parser_create',
        'eval' => 'php_eval',
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
        } else {
            $type   = 'Discouraged';
            $error .= 'discouraged';
        }

        if ($pattern === null) {
            $pattern = $function;
        }

        if ($this->forbiddenFunctions[$pattern] !== null
          && $this->forbiddenFunctions[$pattern] !== 'null'
        ) {
            $type  .= 'WithAlternative';
            $data[] = $this->forbiddenFunctions[$pattern];
            $error .= '; use %s() instead';
        }

        if ($this->error === true) {
            $fix = $phpcsFile->addFixableError($error, $stackPtr, $type, $data);
        } else {
            $fix = $phpcsFile->addFixableWarning($error, $stackPtr, $type, $data);
        }
        if ($fix) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($stackPtr, $this->forbiddenFunctions[$pattern]);
            $phpcsFile->fixer->endChangeset();
        }

    }//end addError()

}//end class
