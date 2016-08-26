<?php

/**
 * This sniff prevents kleenex.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   DrupalSecure
 * @author    Ben Jeavons <ben.jeavons@acquia.com>
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class DrupalSecure_Sniffs_XSS_DrupalSetTitleSniff extends DrupalSecure_Sniffs_General_AbstractFunction
{
    /**
     * Returns an array of function names this test wants to listen for.
     *
     * @return array
     */
    public function registerFunctionNames() {
        // Register the Drupal output functions to scan.
        return array('drupal_set_title');
    }

    public function processFunctionCall(DrupalSecure_Sniffs_General_HelperSniff $sniff, $stackPtr) {
        return;
        //print "processing " . $sniff->tokens[$stackPtr]['content'] . "\n";
        
        // check if token content is function call and function we care about
        // Get function arguments and determine if it's user input and has not been
        // sanitized or filtered. 

        // Functions have different arguments that need to be checked.
        $argument = $sniff->getFunctionCallArgument($stackPtr, 1);
        //print_r($sniff->tokens[$argument['start']]);
        //print_r($tokens[$argument['start']]);
        if ($sniff->tokens[$argument['start']]['code'] === T_STRING && $sniff->isFunctionCall($argument['start'])) {
            if ($sniff->isFunctionUserInput($argument['start'])) {
                $error = 'Input to %s is unsanitized from %s';
                $sniff->phpcsFile->addError($error, $stackPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content']), trim($sniff->tokens[$argument['start']]['content'])));
            }
            else {
              $this->processFunctionCall($sniff, $argument['start']);
            }
        }
        elseif ($sniff->tokens[$argument['start']]['code'] === T_VARIABLE) {
            if ($sniff->isVariableUserInput($argument['start'])) {
                $error = 'Input to %s is unsanitized from %s';
                $sniff->phpcsFile->addError($error, $stackPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content']), trim($sniff->tokens[$argument['start']]['content'])));
                return;
            } 
            $value = $sniff->tokens[$argument['start']]['content'];
            $assignment = $sniff->getVariableAssignment($argument['start'], $value);
            if (empty($assignment)) {
                // Variable isn't assigned in this scope.
                return;
            }
            switch ($sniff->tokens[$assignment]['code']) {
              case T_VARIABLE:
                // determine if variable is specific input
                if ($sniff->isVariableUserInput($assignment)) {
                  $error = 'Input to %s is unsanitized from %s';
                  $sniff->phpcsFile->addError($error, $stackPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content']), trim($sniff->tokens[$assignment]['content'])));
                }       
                // @todo call checkVariable again
                return;
              case T_CONSTANT_ENCAPSED_STRING:
              case T_DOUBLE_QUOTED_STRING:
                // @todo hard-coded value
                return;
              case T_STRING:
                if ($sniff->isFunctionCall($assignment)) { 
                  if ($sniff->isFunctionUserInput($assignment)) {
                        $error = 'Input to %s is unsanitized from %s';
                        $sniff->phpcsFile->addError($error, $stackPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content']), trim($sniff->tokens[$assignment]['content'])));
                    }
                    else {
                      $this->processFunctionCall($sniff, $argument['start']);
                    }
                }
                return;
            }
        }
    }

}

?>
