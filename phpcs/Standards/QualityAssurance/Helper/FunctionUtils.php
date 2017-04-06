<?php
/**
 * QualityAssurance_Helper_FunctionUtils.
 *
 * Helper class to sniff for specific function calls.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Helper_FunctionUtils
{
  protected $openBracket;
  protected $arguments;
  protected $closeBracket;
  protected $functionCall;
  protected $includeMethodCalls = false;
  protected $phpcsFile;
  protected $stackPtr;
  protected $tokens;

  /**
   * QualityAssurance_Helper_FunctionCall constructor.
   *
   * @param \PHP_CodeSniffer_File $phpcsFile
   * @param int $stackPtr
   */
  public function __construct(\PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
    $tokens = $phpcsFile->getTokens();
    $openBracket =  $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);
    $this->phpcsFile = $phpcsFile;
    $this->stackPtr = $stackPtr;
    $this->openBracket = $openBracket;
    $this->closeBracket = $tokens[$openBracket]['parenthesis_closer'];
    $this->arguments = [];
    $this->tokens = $tokens;
  }

  /**
   * Checks if this is a function call.
   *
   * @return bool
   */
  protected function isFunctionCall()
  {
    // Find the next non-empty token.
    $openBracket = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($this->stackPtr + 1), null, true);

    if ($this->tokens[$this->openBracket]['code'] !== T_OPEN_PARENTHESIS) {
      // Not a function call.
      return false;
    }

    if (isset($this->tokens[$openBracket]['parenthesis_closer']) === false) {
      // Not a function call.
      return false;
    }

    // Find the previous non-empty token.
    $search   = PHP_CodeSniffer_Tokens::$emptyTokens;
    $search[] = T_BITWISE_AND;
    $previous = $this->phpcsFile->findPrevious($search, ($this->stackPtr - 1), null, true);
    if ($this->tokens[$previous]['code'] === T_FUNCTION) {
      // It's a function definition, not a function call.
      return false;
    }

    if ($this->tokens[$previous]['code'] === T_OBJECT_OPERATOR && $this->includeMethodCalls === false) {
      // It's a method invocation, not a function call.
      return false;
    }

    if ($this->tokens[$previous]['code'] === T_DOUBLE_COLON && $this->includeMethodCalls === false) {
      // It's a static method invocation, not a function call.
      return false;
    }

    return true;
  }

  /**
   * Returns start and end token for a given argument number.
   *
   * @param int $number
   * Indicates which argument should be examined, starting with
   * 1 for the first argument.
   *
   * @return array(string => int)
   */
  public function getArgument($number)
  {
    // Check if we already calculated the tokens for this argument.
    if (isset($this->arguments[$number]) === true) {
      return $this->arguments[$number];
    }

    $tokens = $this->tokens;
    // Start token of the first argument.
    $start = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($this->openBracket + 1), null, true);
    if ($start === $this->closeBracket) {
      // Function call has no arguments, so return false.
      return false;
    }

    // End token of the last argument.
    $end = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($this->closeBracket - 1), null, true);

    $lastArgEnd = $end;
    $nextSeperator = $this->openBracket;
    $counter = 1;

    while (($nextSeperator = $this->phpcsFile->findNext(T_COMMA, ($nextSeperator + 1), $this->closeBracket)) !== false) {

      // Make sure the comma belongs directly to this function call,
      // and is not inside a nested function call or array.
      $brackets    = $tokens[$nextSeperator]['nested_parenthesis'];
      $lastBracket = array_pop($brackets);
      if ($lastBracket !== $this->closeBracket) {
        continue;
      }

      // Update the end token of the current argument.
      $end =  $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($nextSeperator - 1), null, true);
      // Save the calculated findings for the current argument.
      $this->arguments[$counter] = array(
        'start' => $start,
        'end'   => $end,
      );
      if ($counter === $number) {
        break;
      }

      $counter++;
      $start =  $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($nextSeperator + 1), null, true);
      $end   = $lastArgEnd;
    }//end while

    // If the counter did not reach the passed number something is wrong.
    if ($counter !== $number) {
      return false;
    }

    $this->arguments[$counter] = array(
      'start' => $start,
      'end'   => $end,
    );
    return $this->arguments[$counter];

  }

}
