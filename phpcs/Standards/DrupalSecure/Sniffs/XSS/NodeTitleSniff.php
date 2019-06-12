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
class DrupalSecure_Sniffs_XSS_NodeTitleSniff extends DrupalSecure_Sniffs_General_AbstractObjectProperty
{
  /**
   *
   */
    public function registerObjectProperty()
    {
        return array('title');
    }

  /**
   *
   */
    public function processObjectProperty(DrupalSecure_Sniffs_General_HelperSniff $sniff, $stackPtr)
    {
        // Confirm this is a property of a node. @todo
        if ($sniff->isFunctionCall($stackPtr)) {
            return;
        }
        if ($sniff->isBeingPrinted($stackPtr) && !$sniff->isBeingSanitized($stackPtr)) {
            $error = 'Printing unsanitized input from node %s';
            $sniff->phpcsFile->addError($error, $stackPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content'])));
        } elseif (($variablePtr = $sniff->isBeingAssigned($stackPtr)) !== false) {
            // @todo also check if assigned to array element
            $nextPtr = $variablePtr;
            while (($nextPtr = $sniff->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $nextPtr + 1, null, true, $sniff->tokens[$variablePtr]['content'])) !== false) {
                if ($sniff->isBeingPrinted($nextPtr) && !$sniff->isBeingSanitized($nextPtr)) {
                    $error = 'Printing unsanitized input from node %s';
                    $sniff->phpcsFile->addError($error, $nextPtr, 'DangerousUserInput', array(trim($sniff->tokens[$nextPtr]['content'])));
                } //elseif ($sniff->isBeingReturned($nextPtr)) {// && ($definitionPtr = $sniff->isWithinFunction($nextPtr)) !== false) {
                elseif ($sniff->isBeingReturned($nextPtr)) {// && ($definitionPtr = $sniff->isWithinFunction($nextPtr)) !== false) {
                    $error = 'Returning unsanitized input from node title in %s';
                    $sniff->phpcsFile->addWarning($error, $nextPtr, 'DangerousUserInput', array(trim($sniff->tokens[$nextPtr]['content'])));//, trim($sniff->tokens[$definitionPtr]['content'])));
                }
            }
        } elseif ($sniff->isBeingReturned($stackPtr) && ($definitionPtr = $sniff->isWithinFunction($stackPtr)) !== false) {
            if (($outputPtr = $sniff->isPrinted($definitionPtr)) !== false) {
                $error = 'Printing unsanitized input from node %s set within function %s';
                $sniff->phpcsFile->addError($error, $outputPtr, 'DangerousUserInput', array(trim($sniff->tokens[$stackPtr]['content']), trim($sniff->tokens[$definitionPtr]['content'])));
            }
        }
    }
}
