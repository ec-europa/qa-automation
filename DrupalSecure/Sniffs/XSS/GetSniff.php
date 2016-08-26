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

class DrupalSecure_Sniffs_XSS_GetSniff extends DrupalSecure_Sniffs_General_AbstractVariable
{
  /**
   *
   */
  public function registerVariable() {
    return array('$_GET');
  }

  /**
   *
   */
  public function processVariable(DrupalSecure_Sniffs_General_HelperSniff $sniff, $stackPtr) {
    // Trace where $_GET goes.
    if ($sniff->isBeingPrinted($stackPtr) && !$sniff->isBeingSanitized($stackPtr)) {
      $error = 'Printing unsanitized input from %s';
      $sniff->phpcsFile->addError($error, $stackPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content'])));
    }
    elseif (($variablePtr = $sniff->isBeingAssigned($stackPtr)) !== false) {
      // @todo also check if assigned to array element
      $next = $sniff->phpcsFile->findNext(T_VARIABLE, $stackPtr, null, false, $sniff->tokens[$variablePtr]['content']);
      if ($sniff->isBeingPrinted($next)) {
        $error = 'Printing unsanitized input in %s set from %s';
        $sniff->phpcsFile->addError($error, $next, 'DangerousUserInput', array(trim($sniff->tokens[$variablePtr]['content']), trim($sniff->tokens[$stackPtr]['content'])));
      }
    }
    elseif ($sniff->isBeingReturned($stackPtr) && ($definitionPtr = $sniff->isWithinFunction($stackPtr)) !== false) {
      if (($outputPtr = $sniff->isPrinted($definitionPtr)) !== false) {
        $error = 'Printing unsanitized input from %s set within function %s';
        $sniff->phpcsFile->addError($error, $outputPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content']), trim($sniff->tokens[$definitionPtr]['content'])));
      }
    }
  }
}

?>
