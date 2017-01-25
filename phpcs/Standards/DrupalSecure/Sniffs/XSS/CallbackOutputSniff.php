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
class DrupalSecure_Sniffs_XSS_CallbackOutputSniff extends DrupalSecure_Sniffs_General_AbstractString {

  public function registerString() {
    return array('page callback');
  }
  
  public function processString(DrupalSecure_Sniffs_General_HelperSniff $sniff, $stackPtr) {
    $definitionPtr = $sniff->isWithinFunction($stackPtr);
    if ($definitionPtr === false || !strpos($sniff->tokens[$definitionPtr]['content'], '_menu')) {
      return;
    }
    if (($next = $sniff->isArrayKeyAssigned($stackPtr)) !== false) {
      if ($sniff->tokens[$next]['code'] == T_CONSTANT_ENCAPSED_STRING || $sniff->tokens[$next]['code'] == T_DOUBLE_QUOTED_STRING) {
        // Find function definition.
        $callbackPtr = $next;
        $callback = $sniff->stripString($sniff->tokens[$callbackPtr]['content']);
        // Some callbacks can be safely ignored.
        if (in_array($callback, array('drupal_get_form'))) {
          return;
        }
        $definitionPtr = $sniff->getFunctionDefinition($callback);
        $arguments = $sniff->getFunctionDefinitionArguments($definitionPtr);
        // Callbacks without arguments are safe and can be ignored.
        if (empty($arguments)) {
          return;
        }
        // Use closing function bracket to limit search scope.
        $functionPtr = $sniff->phpcsFile->findPrevious(T_FUNCTION, $definitionPtr - 1, null);
        $closeBracket = $sniff->tokens[$functionPtr]['scope_closer'];
        foreach ($arguments as $argumentPtr) {
          $nextPtr = $argumentPtr;
          $prevPtr = $argumentPtr;
          while (($nextPtr = $sniff->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $nextPtr + 1, $closeBracket, true, $sniff->tokens[$argumentPtr]['content'])) !== false) {
            if (($assignedPtr = $sniff->isAssignment($nextPtr)) !== false) {
              // Variable is being re-assigned.
              if ($sniff->tokens[$assignedPtr]['code'] === T_STRING && in_array($sniff->tokens[$assignedPtr]['content'], $sniff->sanitizationFunctions())) {
                // Variable was over-written with something sanitized, stop
                // further processing.
                return;
              }
            }
            elseif ($sniff->isBeingPrinted($nextPtr) !== false && !$sniff->isBeingSanitized($nextPtr)) {
              $error = 'Menu callback %s of function %s is printed';
              $sniff->phpcsFile->addError($error, $nextPtr, 'XSS.CallbackOutput', array(trim($sniff->tokens[$argumentPtr]['content']), trim($callback)));
            }
            elseif (($outputPtr = $sniff->isAnArgument($nextPtr, $prevPtr)) !== false) {
              if (in_array($sniff->tokens[$outputPtr]['content'], $sniff->outputFunctions())) {
                $error = 'Menu callback argument %s of function %s output with %s';
                $sniff->phpcsFile->addError($error, $outputPtr, 'XSS.CallbackOutput', array(trim($sniff->tokens[$argumentPtr]['content']), trim($callback), $sniff->tokens[$outputPtr]['content']));
              }
              elseif ($sniff->isBeingSanitized($outputPtr)) {
                // @todo ?
              }
            }
            elseif (($variablePtr = $sniff->isBeingAssigned($nextPtr)) !== false) {
              // @todo trace new variable.
            }
            elseif ($sniff->isBeingReturned($nextPtr, $closeBracket)) {// && !$sniff->isBeingSanitized($outputPtr)) {
              $error = 'Menu callback argument %s returned from %s';
              $sniff->phpcsFile->addError($error, $nextPtr, 'XSS.CallbackOutput', array(trim($sniff->tokens[$argumentPtr]['content']), trim($callback)));
            }
            $prevPtr = $nextPtr;
          }
        }
      }
    }
  }
}
