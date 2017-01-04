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
class DrupalSecure_Sniffs_XSS_OutputSniff extends DrupalSecure_Sniffs_General_AbstractOutput
{
  public function registerOutput() {
    return array(T_ECHO, T_PRINT);
  }

  public function processOutput(DrupalSecure_Sniffs_General_HelperSniff $sniff, $stackPtr) {
//print_r($sniff->tokens);
return;
        if ($sniff->tokens[$stackPtr]['code'] === T_STRING && $sniff->isFunctionCall($stackPtr)) {
            if ($sniff->isFunctionUserInput($stackPtr)) {
                $error = 'Output is unsanitized from %s';
                $sniff->phpcsFile->addError($error, $stackPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content'])));
            }
            else {
              $return = $sniff->getFunctionReturn($sniff->tokens[$stackPtr]['content']);
              if ($return && $sniff->isVariableUserInput($return)) {
                $error = 'Output is unsanitized from %s';
                $sniff->phpcsFile->addError($error, $stackPtr, 'DangerousUserInput', array(trim($sniff->tokens[$return]['content'])));
              }
              //print $sniff->tokens[$definition]['content'];
              //print $sniff->phpcsFile->getTokensAsString($definition, 4);
            }
        }
        elseif ($sniff->tokens[$stackPtr]['code'] === T_VARIABLE) {
            // determine if variable is specific input
            if ($sniff->isVariableUserInput($stackPtr)) {
              $error = 'Output is unsanitized from %s';
              $sniff->phpcsFile->addError($error, $stackPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content'])));
            }
            else {
              // @todo trace variable assignment
              if (($next = $sniff->isVariableArray($stackPtr)) !== false) {
                $assignment = $sniff->getVariableAssignment($stackPtr, $sniff->tokens[$stackPtr]['content'], $next);
                if ($sniff->isVariableUserInput($assignment)) {
                  $error = 'Output is unsanitized from %s';
                  $sniff->phpcsFile->addError($error, $stackPtr, 'DangerousUserInput', array(trim($sniff->tokens[$assignment]['content'])));
                }
              }
            }
        }
  }

}
