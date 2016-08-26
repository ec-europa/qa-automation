<?php
/**
 * This sniff checks for user input passed to PHP's unserialize.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   DrupalSecure
 * @author    Ben Jeavons <ben.jeavons@acquia.com>
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class DrupalSecure_Sniffs_CodeExec_UnserializeSniff extends DrupalSecure_Sniffs_General_AbstractFunction {
  
  public function registerFunctionNames() {
    return array('unserialize');
  }

  public function processFunctionCall(DrupalSecure_Sniffs_General_HelperSniff $sniff, $stackPtr) {
    $argument = $sniff->getFunctionCallArgument($stackPtr, 1);
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
    }
  }
}
