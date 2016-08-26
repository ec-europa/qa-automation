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
class DrupalSecure_Sniffs_SQLi_QuerySniff extends DrupalSecure_Sniffs_General_AbstractFunction
//class DrupalSecure_Sniffs_Security_OutputSniff implements PHP_CodeSniffer_Sniff
{
  public function registerFunctionNames() {
    return array('db_query');
  }

  public function processFunctionCall(DrupalSecure_Sniffs_General_HelperSniff $sniff, $stackPtr) {
//print_r($sniff->tokens);
    $openBracket = $sniff->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);
    $nextPtr = $sniff->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($openBracket + 1), null, true);
    if ($sniff->tokens[$nextPtr]['code'] === T_VARIABLE) {
      // @todo trace variable
    }
    elseif ($sniff->tokens[$nextPtr]['code'] == T_CONSTANT_ENCAPSED_STRING || $sniff->tokens[$nextPtr]['code'] == T_DOUBLE_QUOTED_STRING) {
      $nextPtr = $sniff->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($nextPtr + 1), null, true);
      if ($sniff->tokens[$nextPtr]['code'] === T_STRING_CONCAT) {
        $nextPtr = $sniff->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($nextPtr + 1), null, true);
        // @todo trace variable
        $message = 'Possible SQL injection in db_query() via variable %s';
        $sniff->phpcsFile->addWarning($message, $stackPtr, 'SQLi.dbQuery', array(trim($sniff->tokens[$nextPtr]['content'])));
      }
    }
  }

}
