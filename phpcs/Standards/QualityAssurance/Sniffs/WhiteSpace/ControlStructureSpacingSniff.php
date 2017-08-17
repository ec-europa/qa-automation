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
class QualityAssurance_Sniffs_WhiteSpace_ControlStructureSpacingSniff extends Squiz_Sniffs_WhiteSpace_ControlStructureSpacingSniff implements PHP_CodeSniffer_Sniff
{

  /**
   * Returns an array of tokens this test wants to listen for.
   *
   * @return array
   */
  public function register()
  {
    return array_merge(
      parent::register(),
      array(T_FUNCTION, T_CLASS, T_INTERFACE)
    );
  }//end register()

}//end class

?>

