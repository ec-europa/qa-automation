<?php

/**
 * This sniff checks for CSRF.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   DrupalSecure
 * @author    Ben Jeavons <ben.jeavons@acquia.com>
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class DrupalSecure_Sniffs_CSRF_QuerySniff extends DrupalSecure_Sniffs_General_AbstractFunction
{
  public function registerFunctionNames() {
    return array('db_query');
  }

  public function processFunctionCall(DrupalSecure_Sniffs_General_HelperSniff $sniff, $stackPtr) {
    return;
  }

}
