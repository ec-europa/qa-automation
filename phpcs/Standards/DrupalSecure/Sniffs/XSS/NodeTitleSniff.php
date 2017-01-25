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
class DrupalSecure_Sniffs_XSS_NodeTitleSniff extends DrupalSecure_Sniffs_General_AbstractObjectProperty {
  /**
   *
   */
  public function registerObjectProperty() {
    return array('title');
  }

  /**
   *
   */
  public function processObjectProperty(DrupalSecure_Sniffs_General_HelperSniff $sniff, $stackPtr) {
    // Confirm this is a property of a node. @todo
    if ($sniff->isFunctionCall($stackPtr)) {
      return;
    }
    if ($sniff->isBeingPrinted($stackPtr) && !$sniff->isBeingSanitized($stackPtr)) {
      $error = 'Printing unsanitized input from node %s';
      $sniff->phpcsFile->addError($error, $stackPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content'])));
    }
    elseif (($variablePtr = $sniff->isBeingAssigned($stackPtr)) !== false) {
      // @todo also check if assigned to array element
      $nextPtr = $variablePtr;
      while (($nextPtr = $sniff->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $nextPtr + 1, null, true, $sniff->tokens[$variablePtr]['content'])) !== false) {
        if ($sniff->isBeingPrinted($nextPtr) && !$sniff->isBeingSanitized($nextPtr)) {
          $error = 'Printing unsanitized input from node %s';
          $sniff->phpcsFile->addError($error, $nextPtr, 'DangerousUserInput', array(trim($sniff->tokens[$nextPtr]['content'])));
        }
        //elseif ($sniff->isBeingReturned($nextPtr)) {// && ($definitionPtr = $sniff->isWithinFunction($nextPtr)) !== false) {
        elseif ($sniff->isBeingReturned($nextPtr)) {// && ($definitionPtr = $sniff->isWithinFunction($nextPtr)) !== false) {
          $error = 'Returning unsanitized input from node title in %s';
          $sniff->phpcsFile->addWarning($error, $nextPtr, 'DangerousUserInput', array(trim($sniff->tokens[$nextPtr]['content'])));//, trim($sniff->tokens[$definitionPtr]['content'])));
        }
        elseif (($outputPtr = $sniff->isAnArgument($nextPtr)) !== false && !$sniff->isBeingSanitized($nextPtr)) {
          if (in_array($sniff->tokens[$outputPtr]['content'], $sniff->outputFunctions())) {
            $error = 'Node %s output with %s';
            $sniff->phpcsFile->addError($error, $outputPtr, 'DangerousUserInput', array(trim($sniff->tokens[$nextPtr]['content']), $sniff->tokens[$outputPtr]['content']));
          }
          elseif ($sniff->isBeingSanitized($outputPtr)) {
            // 
          }
        }
      }
      /*$next = $sniff->phpcsFile->findNext(T_VARIABLE, $stackPtr, null, false, $sniff->tokens[$variablePtr]['content']);
      if ($sniff->isBeingPrinted($next)) {
        $error = 'Printing unsanitized input from node %s set from %s';
        $sniff->phpcsFile->addError($error, $next, 'DangerousUserInput', array(trim($sniff->tokens[$variablePtr]['content']), trim($sniff->tokens[$stackPtr]['content'])));
      }
      elseif (($outputPtr = $sniff->isReturned($variablePtr)) !== false) {
        $error = 'Returning unsanitized input from node %s';
        $sniff->phpcsFile->addError($error, $outputPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content'])));
      }*/
    }
    elseif ($sniff->isBeingReturned($stackPtr) && ($definitionPtr = $sniff->isWithinFunction($stackPtr)) !== false) {
      if (($outputPtr = $sniff->isPrinted($definitionPtr)) !== false) {
        $error = 'Printing unsanitized input from node %s set within function %s';
        $sniff->phpcsFile->addError($error, $outputPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content']), trim($sniff->tokens[$definitionPtr]['content'])));
      }
    }
    elseif (($outputPtr = $sniff->isAnArgument($stackPtr)) !== false) {
      if (in_array($sniff->tokens[$outputPtr]['content'], $sniff->outputFunctions())) {
        $error = 'Node %s output with %s';
        $sniff->phpcsFile->addError($error, $outputPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content']), $sniff->tokens[$outputPtr]['content']));
      }
      elseif ($sniff->isBeingSanitized($outputPtr)) {
        // 
      }
    }
  }
  
}
