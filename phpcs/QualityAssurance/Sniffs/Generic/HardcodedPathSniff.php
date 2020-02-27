<?php
/**
 * \QualityAssurance\Sniffs\Generic\HardcodedPathSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\Generic;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * \QualityAssurance\Sniffs\Generic\HardcodedPathSniff.
 *
 * Checks php files for hardcoded paths.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class HardcodedPathSniff implements Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_STRING,
            T_CONSTANT_ENCAPSED_STRING,
            T_INLINE_HTML,
        ];

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token
     *                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $end = (count($phpcsFile->getTokens()) + 1);
        // Check whole project for string "sites/".
        $fileContent = file_get_contents($phpcsFile->getFilename());
        if (strpos($fileContent, 'sites') === false) {
            return $end;
        }

        // If the file extension is equal to '.yml' return.
        $fileName =      $phpcsFile->getFilename();
        $fileExtension = strtolower(substr($fileName, -4));
        if ($fileExtension === '.yml') {
            return $end;
        }

        // Get our tokens.
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];
        // Path regular expression.
        $regexp = 'sites/[^/]+/(files|libraries|modules|themes)';
        // If hardcoded path is found.
        if (preg_match("~$regexp~", $token['content'], $matches) === 1) {
            $error = "Internal hardcoded paths are not allowed. ";
            switch ($matches[1]) {
            case 'modules':
                $error .= "Please use drupal_get_path('module', \$name).";
                break;
            case 'themes':
                $error .= "Please use drupal_get_path('theme', \$name).";
                break;
            case 'files':
                $error .= "Please use \Drupal::service('file_system')->realpath().";
                break;
            }

            $phpcsFile->addError($error, $stackPtr, 'HardcodedPath');
        }

    }//end process()


}//end class
