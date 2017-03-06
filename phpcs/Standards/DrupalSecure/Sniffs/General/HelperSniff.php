<?php
/**
 * DrupalSecure_Sniffs_General_HelperSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Helper class to sniff Drupal/PHP files.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class DrupalSecure_Sniffs_General_HelperSniff implements PHP_CodeSniffer_Sniff
{
  /**
     * Collection of objects that get notified during processing.
     *
     * @var array
     */
    protected static $functionListeners = array();
    protected static $variableListeners = array();
    protected static $outputListeners = array();
    protected static $stringListeners = array();
    protected static $objectPropertyListeners = array();

    /**
     * The currently processed file.
     *
     * @var PHP_CodeSniffer_File
     */
    public $phpcsFile;
    
    public $tokens;
    
    protected $functionCalls = array();
    
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_QUOTED_STRING, T_STRING, T_VARIABLE, T_ECHO, T_PRINT, T_OBJECT_OPERATOR);
    }
  
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $this->phpcsFile = $phpcsFile;
        $this->tokens = $phpcsFile->getTokens();
        //print_r($this->tokens[$stackPtr]);
        if ($this->tokens[$stackPtr]['code'] == T_STRING) {
          $this->processString($stackPtr);
        }
        elseif (in_array($this->tokens[$stackPtr]['code'], array(T_ECHO, T_PRINT))) {
          $this->processOutput($stackPtr);
        }
        elseif ($this->tokens[$stackPtr]['code'] == T_VARIABLE) {
          $this->processVariable($stackPtr);
        }
        elseif ($this->tokens[$stackPtr]['code'] == T_CONSTANT_ENCAPSED_STRING || $this->tokens[$stackPtr]['code'] == T_DOUBLE_QUOTED_STRING) {
          $this->processEncapsedString($stackPtr);
        }
        elseif ($this->tokens[$stackPtr]['code'] == T_OBJECT_OPERATOR) {
          $nextPtr = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);
          $this->processObjectProperty($nextPtr);
        }
    }
    
    private function processEncapsedString($stackPtr) {
      $activeListeners = array();
        foreach (self::$stringListeners as $listener) {
            $types = $listener->registerString();
            if (in_array($this->stripString($this->tokens[$stackPtr]['content']), $types) === true) {
                $activeListeners[] = $listener;
            }
        }

        // No listener is interested in this so return early.
        if (empty($activeListeners) === true) {
            return;
        }
        foreach ($activeListeners as $listener) {
            $listener->processString($this, $stackPtr);
        }
    }
    
    private function processVariable($stackPtr)
    {
      $activeListeners = array();
        foreach (self::$variableListeners as $listener) {
            $types = $listener->registerVariable();
            if (in_array($this->tokens[$stackPtr]['content'], $types) === true) {
                $activeListeners[] = $listener;
            }
        }

        // No listener is interested in this so return early.
        if (empty($activeListeners) === true) {
            return;
        }
        foreach ($activeListeners as $listener) {
            $listener->processVariable($this, $stackPtr);
        }
    }
    
    private function processOutput($stackPtr) {
      $activeListeners = array();
        foreach (self::$outputListeners as $listener) {
            $types = $listener->registerOutput();
            if (in_array($this->tokens[$stackPtr]['code'], $types) === true) {
                $activeListeners[] = $listener;
            }
        }

        // No listener is interested in this function name, so return early.
        if (empty($activeListeners) === true) {
            return;
        }
        $next = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);
        foreach ($activeListeners as $listener) {
            $listener->processOutput($this, $next);
        }
    }
    
    //private function processString(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $tokens)
    private function processString($stackPtr)
    {
        $activeListeners = array();
        foreach (self::$functionListeners as $listener) {
            $functionNames = $listener->registerFunctionNames();
            if (in_array($this->tokens[$stackPtr]['content'], $functionNames) === true) {
                $activeListeners[] = $listener;
            }
        }

        // No listener is interested in this function name, so return early.
        if (empty($activeListeners) === true) {
            return;
        }

        // Find the next non-empty token.
        $openBracket = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);

        if (!$this->isFunctionCall($stackPtr)) {
          return;
        }

        // Find the previous non-empty token.
        $search   = PHP_CodeSniffer_Tokens::$emptyTokens;
        $search[] = T_BITWISE_AND;
        $previous = $this->phpcsFile->findPrevious($search, ($stackPtr - 1), null, true);
        if ($this->tokens[$previous]['code'] === T_FUNCTION) {
            // It's a function definition, not a function call.
            return;
        }

        if ($this->tokens[$previous]['code'] === T_OBJECT_OPERATOR) {
            // It's a method invocation, not a function call.
            return;
        }

        if ($this->tokens[$previous]['code'] === T_DOUBLE_COLON) {
            // It's a static method invocation, not a function call.
            return;
        }

        $this->functionCalls[$stackPtr] = array('openBracket' => $openBracket, 'closeBracket' => $this->tokens[$openBracket]['parenthesis_closer']);

        foreach ($activeListeners as $listener) {
            $listener->processFunctionCall($this, $stackPtr);
        }

    }
  
    private function processObjectProperty($stackPtr) {
      $activeListeners = array();
        foreach (self::$objectPropertyListeners as $listener) {
            $types = $listener->registerObjectProperty();
            if (in_array($this->tokens[$stackPtr]['content'], $types) === true) {
                $activeListeners[] = $listener;
            }
        }

        // No listener is interested in this function name, so return early.
        if (empty($activeListeners) === true) {
            return;
        }
        foreach ($activeListeners as $listener) {
            $listener->processObjectProperty($this, $stackPtr);
        }
    }
  
    /**
     * Remove quote and double-quote characters from beginning and end of string.
     */
    public function stripString($string) {
      return trim($string, '\'"');
    }
  
    public function isVariableUserInput($stackPtr) {
      switch ($this->tokens[$stackPtr]['content']) {
        case '$_GET':
          return true;
        case '$_POST':
          return true;
        default:
          return false;
      }
    }
    
    public function isFunctionUserInput($stackPtr) {
      switch ($this->tokens[$stackPtr]['content']) {
        case 'variable_get':
          return true;
        default:
          return false;
      }
    }
    
  //protected function isFunctionCall(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $tokens) {
    public function isFunctionCall($stackPtr) {
        $openBracket = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true); 
        if ($this->tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
           return false;
        }
        if (isset($this->tokens[$openBracket]['parenthesis_closer']) === false) {
           return false;
        }
        return true;
    }
    
    public function isVariableArray($stackPtr) {
      $nextPtr = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $stackPtr + 1, null, true);
      if ($this->tokens[$nextPtr]['code'] === T_OPEN_SQUARE_BRACKET) {
        return $nextPtr;
      }
      return false;
    }
    
    /**
     * Check if token is being printed.
     *
     * @param integer $stackPtr
     *
     * @return boolean
     */
    public function isBeingPrinted($stackPtr) {
      $prevPtr = $this->phpcsFile->findPrevious(array(T_PRINT, T_ECHO), $stackPtr - 1, null, false, null, true);
      if ($prevPtr !== false) {
        return true;
      }
      else {
        return false;
      }
    }
    
    /**
     * Check if token is printed later in the stack.
     *
     * @param integer $stackPtr Stack pointer to check.
     * @param integer $end Optional end pointer to limit traversal.
     *
     * @return integer or false Stack pointer within print statement or false.
     */
    public function isPrinted($stackPtr, $end = null) {
      // print $foo; echo "bar " . $foo; print "$foo"; print foo();
      // @todo finish
      $nextPtr = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $stackPtr + 1, $end, true, $this->tokens[$stackPtr]['content']);
      if ($nextPtr === false) {
        return false;
      }
      if ($this->isBeingPrinted($nextPtr)) {
        return $nextPtr;
      }
      else {
        return $this->isPrinted($nextPtr, $end);
      }
      /*if ($this->tokens[$nextPtr]['code'] == T_EQUAL) {
        $nextPtr = $this->phpcsFile->findNext(array(T_PRINT, T_ECHO), $nextPtr + 1, null, true);
        return $nextPtr;
      }
      else {
        return false;
      }*/
    }
    
    /**
     * Check if token is an argument to a function call.
     *
     * @param integer $stackPtr
     * * @param integer $end Optional end pointer to limit traversal.
     *
     * @return integer or false Function stack pointer or false.
     */
    public function isAnArgument($stackPtr, $end = null) {
      // foo($bar); foo(array('foo', bar()), $bar);
      $prevPtr = $this->phpcsFile->findPrevious(T_OPEN_PARENTHESIS, $stackPtr - 1, $end, false, null, true);
      if ($prevPtr !== false) {
        $prevPtr = $this->phpcsFile->findPrevious(T_STRING, $prevPtr - 1, $end, false, null, true);
        if ($prevPtr !== false) {
          if ($this->tokens[$prevPtr]['code'] == T_STRING) {
            return $prevPtr;
          }
          elseif ($this->tokens[$prevPtr]['code'] == T_ARRAY) {
            // @todo figure out what to do with this.
          }
        }
      }
      return false;
    }
    
    /**
     * Check if token is being assigned.
     *
     * @param integer $stackPtr
     *
     * @return integer or false Assigned stack pointer or false if not.
     */
    public function isBeingAssigned($stackPtr) {
      $prevPtr = $stackPtr;
      $prevPtr = $this->phpcsFile->findPrevious(T_EQUAL, $stackPtr - 1, null, false, null, true);
      if ($prevPtr !== false) {
        return true;
      }
      /*while (($prevPtr = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, $prevPtr - 1, null, true, null, true)) !== false) {
        if ($this->tokens[$prevPtr]['code'] == T_EQUAL) {
          $prevPtr = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, $prevPtr - 1, null, true);
          return $prevPtr;
        }
      }*/
      return false;
      /*$prevPtr = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, $stackPtr - 1, null, true);
      if ($this->tokens[$prevPtr]['code'] == T_EQUAL) {
        $prevPtr = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, $prevPtr - 1, null, true);
        return $prevPtr;
      }
      else {
        return false;
      }*/
    }
    
    /**
     * Check if token is assigned to a new value.
     *
     * @param integer $stackPtr
     *
     * @return integer or false Assigned stack pointer or false if not.
     */
    public function isAssignment($stackPtr) {
      $nextPtr = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $stackPtr + 1, null, true);
      if ($this->tokens[$nextPtr]['code'] == T_EQUAL) {
        $nextPtr = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $nextPtr + 1, null, true);
        // @todo logic of assignment?
        return $nextPtr;
      }
      else {
        return false;
      }
    }
    
    /**
     * Check if token is being returned.
     *
     * @param integer $stackPtr Stack pointer to check.
     *
     * @return boolean True if pointer is within return statement.
     */
    public function isBeingReturned($stackPtr) {
      $prevPtr = $this->phpcsFile->findPrevious(T_RETURN, $stackPtr - 1, null, false, null, true);
      if ($this->tokens[$prevPtr]['code'] == T_RETURN) {
        return true;
      }
      else {
        return false;
      }
    }
    
    /**
     * Check if token is returned later in the stack.
     *
     * @param integer $stackPtr Stack pointer to check.
     * @param integer $end Optional end pointer to limit traversal.
     *
     * @return integer for assigned stack pointer or false if not.
     */
    public function isReturned($stackPtr, $end = null) {
      $nextPtr = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $stackPtr + 1, $end, true, $this->tokens[$stackPtr]['content']);
      if ($nextPtr === false) {
        return false;
      }
      if ($this->isBeingReturned($nextPtr)) {
        return $nextPtr;
      }
      else {
        return $this->isReturned($nextPtr, $end);
      }
    }
    
    /**
     * Check if token is within the scope of a function definition.
     *
     * @param integer $stackPtr Stack pointer to check.
     *
     * @return integer or false Function definition stack pointer or false.
     */
    public function isWithinFunction($stackPtr) {
      // traverse up previous tokens looking for function definition
      $prevPtr = $this->phpcsFile->findPrevious(array(T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET), $stackPtr - 1);
      if ($this->tokens[$prevPtr]['code'] == T_OPEN_CURLY_BRACKET) {
        // @todo check if function/method or class?
        $prevPtr = $this->phpcsFile->findPrevious(T_FUNCTION, $prevPtr - 1);
        return $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $prevPtr + 1, null, true);
      }
      elseif ($this->tokens[$prevPtr]['code'] == T_CLOSE_CURLY_BRACKET) {
        return false;
      }
    }
    
    /**
     * Check if token is key assignment in an array.
     *
     * @param integer $stackPtr Stack pointer to check.
     *
     * @return integer or False Array value for key pointer or false.
     */
    public function isArrayKeyAssigned($stackPtr) {
      $next = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);
      if ($this->tokens[$next]['code'] === T_DOUBLE_ARROW) {
        return $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($next + 1), null, true);
      }
      elseif ($this->tokens[$next]['code'] === T_CLOSE_SQUARE_BRACKET) {
        $next = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($next + 1), null, true);
        if ($this->tokens[$next]['code'] === T_EQUAL) {
          return $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($next + 1), null, true);
        }
        elseif ($this->tokens[$next]['code'] === T_OPEN_SQUARE_BRACKET) {
          return $next;
        }
      }
      elseif ($this->tokens[$next]['code'] === T_COMMA) {
        return FALSE;
      }
    }
    
    public function sanitizationFunctions() {
      $functions = array(
        'check_plain',
        'check_markup',
        'filter_xss',
        'filter_xss_admin',
        'strip_tags',
      );
      return $functions;
    }

    /**
     * Determine if $stackPtr is part of sanitization process.
     */
    public function isBeingSanitized($stackPtr) {
      //$prevPtr = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, $stackPtr - 1, null, true, null, true);
      $prevPtr = $stackPtr;
      //$parens = 0;
      while (($prevPtr = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, $prevPtr - 1, null, true, null, true)) !== false) {
        if ($this->tokens[$prevPtr]['code'] === T_OPEN_PARENTHESIS) {// && $parens > 0) {
          $prevPtr = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, $prevPtr - 1, null, true, null, true);
          if ($this->tokens[$prevPtr]['code'] === T_STRING) {
            if (in_array($this->tokens[$prevPtr]['content'], $this->sanitizationFunctions())) {
              return true;
            }
          }
        }
        elseif ($this->tokens[$prevPtr]['code'] === T_CLOSE_PARENTHESIS) {
          // @todo do something fancy
        }
      }
      return false;
      /*if ($this->tokens[$prevPtr]['code'] === T_OPEN_PARENTHESIS) {
        $prevPtr = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, $prevPtr - 1, null, true, null, true);
        if ($this->tokens[$prevPtr]['code'] === T_STRING) {
          if (in_array($this->tokens[$prevPtr]['content'], $this->sanitizationFunctions())) {
            return true;
          }
        }
      }
      elseif ($this->tokens[$prevPtr]['code'] === T_OBJECT_OPERATOR) {
        $prevPtr = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, $prevPtr - 1, null, true, null, true);
        if ($this->tokens[$prevPtr]['code'] === T_STRING) {
          if (in_array($this->tokens[$prevPtr]['content'], $this->sanitizationFunctions())) {
            return true;
          }
        }
      }
      return false;*/
    }
    
    /**
     *
     */
    public function wasSanitized($stackPtr) {
      
    }
    
    /*
     * Returns start and end token for a given argument number.
     *
     * @param int $number Indicates which argument should be examined, starting with
     *                    1 for the first argument.
     *
     * @return array(string => int)
     */
    public function getFunctionCallArgument($stackPtr, $number)
    {
        // Check if the tokens for function arguments have already been found.
        if (isset($this->functionCalls[$stackPtr][$number]) === true) {
            return $this->functionCalls[$stackPtr][$number];
        }
        $this->functionCalls[$stackPtr] = array();

        // Find the next non-empty token.
        $openBracket = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);
        $closeBracket = $this->tokens[$openBracket]['parenthesis_closer'];

        if ($this->tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        if (isset($this->tokens[$openBracket]['parenthesis_closer']) === false) {
            return;
        }
        
        // Find the previous non-empty token.
        $search   = PHP_CodeSniffer_Tokens::$emptyTokens;
        $search[] = T_BITWISE_AND;
        $previous = $this->phpcsFile->findPrevious($search, ($stackPtr - 1), null, true);
        if ($this->tokens[$previous]['code'] === T_FUNCTION) {
            // It's a function definition, not a function call.
            return;
        }

        if ($this->tokens[$previous]['code'] === T_OBJECT_OPERATOR) {
            // It's a method invocation, not a function call.
            return;
        }

        if ($this->tokens[$previous]['code'] === T_DOUBLE_COLON) {
            // It's a static method invocation, not a function call.
            return;
        }

        // Start token of the first argument.
        $start = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($openBracket + 1), null, true);
        if ($start === $closeBracket) {
            // Function call has no arguments, so return false.
            return false;
        }

        // End token of the last argument.
        $end = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($closeBracket - 1), null, true);
        $lastArgEnd    = $end;
        $nextSeperator = $openBracket;
        $counter       = 1;
        while (($nextSeperator = $this->phpcsFile->findNext(T_COMMA, ($nextSeperator + 1), $closeBracket)) !== false) {
            // Make sure the comma belongs directly to this function call,
            // and is not inside a nested function call or array.
            $brackets    = $this->tokens[$nextSeperator]['nested_parenthesis'];
            $lastBracket = array_pop($brackets);
            if ($lastBracket !== $closeBracket) {
                continue;
            }

            // Update the end token of the current argument.
            $end = $this->phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($nextSeperator - 1), null, true);
            // Save the calculated findings for the current argument.
            $this->functionCalls[$stackPtr][$counter] = array(
                                          'start' => $start,
                                          'end'   => $end,
                                         );
            if ($counter === $number) {
                break;
            }

            $counter++;
            $start = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($nextSeperator + 1), null, true);
            $end   = $lastArgEnd;
        }

        // If the counter did not reach the passed number something is wrong.
        if ($counter !== $number) {
            return false;
        }

        $this->functionCalls[$stackPtr][$counter] = array(
                                      'start' => $start,
                                      'end'   => $end,
                                     );
        return $this->functionCalls[$stackPtr][$counter];
    }
    
    public function getFunctionReturn($functionString, $start = null) {
      $defPtr = $this->getFunctionDefinition($functionString);
      if (empty($defPtr)) {
        return false;
      }
      $returnPtr = $this->phpcsFile->findNext(T_RETURN, $defPtr + 1);
      $nextPos = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $returnPtr + 1, null, true, null, true);
      return $nextPos;
    }
    
    function getFunctionDefinition($functionString) {
      foreach ($this->tokens as $pos => $token) {
        if ($token['code'] == T_FUNCTION) {
          $defPtr = $this->phpcsFile->findNext(T_STRING, $pos, null, false, $functionString);
          if ($defPtr) {
            break;
          }
        }
      }
      if (empty($defPtr)) {
        return false;
      }
      // @todo further verify this is a function definition.
      return $defPtr;
    }
    
    function getFunctionDefinitionArguments($stackPtr) {
      $openBracket = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);
      $closeBracket = isset($this->tokens[$openBracket]['parenthesis_closer']) ? $this->tokens[$openBracket]['parenthesis_closer'] : false;

      if ($this->tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
        return false;
      }

      if (isset($this->tokens[$openBracket]['parenthesis_closer']) === false) {
        return false;
      }
      
      // Start token of the first argument.
        $start = $this->phpcsFile->findNext(T_VARIABLE, ($openBracket + 1), $closeBracket);
        if ($start === false) {
            // Function call has no arguments, so return false.
            return false;
        }

        $arguments = array($start);
        while (($nextArgument = $this->phpcsFile->findNext(T_VARIABLE, ($start + 1), $closeBracket)) !== false) {
          $arguments[] = $nextArgument;
          $start = $nextArgument;
        }
        return $arguments;
    }
    
    /**
     * Get pointer to closing of function definition.
     *
     * @param integer $stackPtr
     * @return integer Stack pointer to closing bracket.
     */
    public function getFunctionCloseBracket($stackPtr) {
      //$closingBracket = $this->phpcsFile->findNext(T_COMMA, $stackPtr + 1, null
      //$prevPtr = $this->phpcsFile->findPrevious(array(T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET), $stackPtr - 1);
    }
    
    public function getVariableAssignment($stackPtr, $variable, $element = null)
    {
      print 'Getting assignment for variable ' . $variable . "\n";
       $found = false;
       while ($found !== true) {
                $pos = $this->phpcsFile->findPrevious(T_VARIABLE, $stackPtr - 1, null, false, $variable);
                //print_r($this->tokens[$pos]). "\n";
                if ($pos !== false) {
                    $nextPos = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $pos + 1, null, true);
                    //print_r($this->tokens[$nextPos]). "\n";
                    if ($this->tokens[$nextPos]['code'] === T_OPEN_SQUARE_BRACKET) {
                      if (isset($element) && $element == $nextPos) {
                        // Find where array element was set.
                      }
                    }
                    // @todo method operator
                    elseif ($this->tokens[$nextPos]['code'] !== T_EQUAL) {
                      // not assigning the variable
                      return;///continue;
                    }
                    elseif ($this->tokens[$nextPos]['code'] === T_CLOSE_PARENTHESIS) {
                      if ($this->isFunctionArgument($nextPos)) {
                        //
                      }
                    }
                    $pos = $this->phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $nextPos + 1, null, true);
                    //print_r($this->tokens[$pos]). "\n";
                    return $pos;
                }
                else {
                    // Hit the stack beginning, don't continue.
                    return false; // @todo make exception
                }
            }
    }
    
    public function stringContainsVariable($stackPtr) {
      //print "looking for variable in " . $this->tokens[$stackPtr]['content'] . "\n";
      // @todo figure this out
    }
    
    /**
     * Registers a listener object so that it will be called during processing.
     *
     * @param DrupalSecure_Sniffs_Drupal_AbstractDrupal $listener
     *   The listener object that should be notified.
     *
     * @return void
     */
    public static function registerFunctionListener($listener)
    {
        self::$functionListeners[] = $listener;
    }
    
    public static function registerVariableListener($listener)
    {
        self::$variableListeners[] = $listener;
    }
    
    public static function registerOutputListener($listener)
    {
        self::$outputListeners[] = $listener;
    }
    
    public static function registerStringListener($listener)
    {
        self::$stringListeners[] = $listener;
    }
    
    public static function registerObjectPropertyListener($listener)
    {
        self::$objectPropertyListeners[] = $listener;
    }
}
