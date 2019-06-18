<?php
/**
 * QualityAssurance_Sniffs_Theming_PrintMessagesSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Check if page template prints the $messages variable.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Theming_PrintMessagesSniff implements PHP_CodeSniffer_Sniff
{
  /**
   * Returns an array of tokens this test wants to listen for.
   *
   * @return array
   */
    public function register()
    {
        return array(
        T_STRING,
        T_CONSTANT_ENCAPSED_STRING,
        T_INLINE_HTML
        );
    }//end register()


  /**
   * Processes this test, when one of its tokens is encountered.
   *
   * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
   * @param int                  $stackPtr  The position of the current token in the
   *                                        stack passed in $tokens.
   *
   * @return void
   */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // Check whole project for string "sites/".
        $file_name = substr($phpcsFile->getFilename(), strrpos($phpcsFile->getFilename(), '/') + 1);
        $file_content = file_get_contents($phpcsFile->getFilename());
        $end = (count($phpcsFile->getTokens()) + 1);

        if (!preg_match('~^page.*\.tpl\.php$~', $file_name) && $file_name !== 'PrintMessagesError.tpl.php') {
            return $end;
        }

        if (strpos($file_content, '<?php print $messages; ?>') === false) {
            $phpcsFile->addError('Page template does not print the $messages variable.', $stackPtr, 'PrintMessages');
        }

        return $end;
    }//end process()
}//end class
