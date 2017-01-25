<?php
/**
 * This sniff just prints the tokens.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   DrupalSecure
 * @author    Ben Jeavons <ben.jeavons@acquia.com>
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

class DrupalSecure_Sniffs_General_OutputTokensSniff implements PHP_CodeSniffer_Sniff
{
  public function register()
  {
        return array(T_DOUBLE_ARROW);
  }
    
  public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
  {
        //print_r($phpcsFile->getTokens());
  }
}

?>
