<?php
/**
 * Ensures no whitespace after a control structure.
 */

/**
 * Ensures no whitespace after a control structure.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_WhiteSpace_ControlStructureSpacingSniff implements PHP_CodeSniffer_Sniff
{

  /**
   * Returns an array of tokens this test wants to listen for.
   *
   * @return array
   */
  public function register()
  {
    return array(T_FUNCTION, T_CLASS, T_INTERFACE);
  }//end register()

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
    $tokens = $phpcsFile->getTokens();

    if (isset($tokens[$stackPtr]['scope_closer']) === false) {
      return;
    }

    $scopeOpener = $tokens[$stackPtr]['scope_opener'];
    $scopeCloser = $tokens[$stackPtr]['scope_closer'];

    for ($firstContent = ($scopeOpener + 1); $firstContent < $phpcsFile->numTokens; $firstContent++) {
      if ($tokens[$firstContent]['code'] !== T_WHITESPACE) {
        break;
      }
    }

    if ($tokens[$firstContent]['line'] >= ($tokens[$scopeOpener]['line'] + 2)) {
      $error = 'Blank line found at start of control structure';
      $fix   = $phpcsFile->addFixableError($error, $scopeOpener, 'SpacingAfterOpen');

      if ($fix === true) {
        $phpcsFile->fixer->beginChangeset();
        $i = ($scopeOpener + 1);
        while ($tokens[$i]['line'] !== $tokens[$firstContent]['line']) {
          $phpcsFile->fixer->replaceToken($i, '');
          $i++;
        }

        $phpcsFile->fixer->addNewline($scopeOpener);
        $phpcsFile->fixer->endChangeset();
      }
    }

    if ($firstContent !== $scopeCloser) {
      $lastContent = $phpcsFile->findPrevious(
        T_WHITESPACE,
        ($scopeCloser - 1),
        null,
        true
      );

      $lastNonEmptyContent = $phpcsFile->findPrevious(
        PHP_CodeSniffer_Tokens::$emptyTokens,
        ($scopeCloser - 1),
        null,
        true
      );

      $checkToken = $lastContent;
      if (isset($tokens[$lastNonEmptyContent]['scope_condition']) === true) {
        $checkToken = $tokens[$lastNonEmptyContent]['scope_condition'];
      }

      if (isset($ignore[$tokens[$checkToken]['code']]) === false
        && $tokens[$lastContent]['line'] <= ($tokens[$scopeCloser]['line'] - 2)
      ) {
        $errorToken = $scopeCloser;
        for ($i = ($scopeCloser - 1); $i > $lastContent; $i--) {
          if ($tokens[$i]['line'] < $tokens[$scopeCloser]['line']) {
            $errorToken = $i;
            break;
          }
        }

        $error = 'Blank line found at end of control structure';
        $fix   = $phpcsFile->addFixableError($error, $errorToken, 'SpacingBeforeClose');

        if ($fix === true) {
          $phpcsFile->fixer->beginChangeset();
          $i = ($scopeCloser - 1);
          for ($i = ($scopeCloser - 1); $i > $lastContent; $i--) {
            if ($tokens[$i]['line'] === $tokens[$scopeCloser]['line']) {
              continue;
            }

            if ($tokens[$i]['line'] === $tokens[$lastContent]['line']) {
              break;
            }

            $phpcsFile->fixer->replaceToken($i, '');
          }

          $phpcsFile->fixer->endChangeset();
        }
      }//end if

    }//end if

  }//end process()

}//end class

?>

