<?php

if (class_exists('PHP_CodeSniffer_Standards_CodingStandard', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_CodingStandard not found');
}

class PHP_CodeSniffer_Standards_DrupalSecure_DrupalSecureCodingStandard extends PHP_CodeSniffer_Standards_CodingStandard
{
    public function getIncludedSniffs()
    {
        return array(
                //'Drupal/Sniffs/Semantics/FunctionCall.php', // doesn't work to get abstract
                //'Drupal/Sniffs/Semantics/FunctionCallSniff.php',
               );
    }
    
    public function getExcludedSniffs()
    {
        return array(
                //'Drupal/Sniffs/Semantics/FunctionCall.php', // doesn't work to get abstract
                'DrupalSecure/Sniffs/Security/GetInputSniff.php',
               );
    }
}

