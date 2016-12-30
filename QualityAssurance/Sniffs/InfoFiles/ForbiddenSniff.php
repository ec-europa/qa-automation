<?php
/**
 * QualityAssurance_Sniffs_InfoFiles_ForbiddenSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * "menu", "php" dependencies and "taxonomy tags" are forbidden in Drupal info files.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_InfoFiles_ForbiddenSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_INLINE_HTML);

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
        // Only run this sniff once per info file.
        $end = (count($phpcsFile->getTokens()) + 1);

        $fileExtension = strtolower(substr($phpcsFile->getFilename(), -4));
        if ($fileExtension !== 'info') {
            return $end;
        }

        $tokens = $phpcsFile->getTokens();

        $contents = file_get_contents($phpcsFile->getFilename());
        $info     = QualityAssurance_Sniffs_InfoFiles_HelperClass::drupalParseInfoFormat($contents);
        if (isset($info['dependencies']) && in_array('php', $info['dependencies'])) {
            $error = 'dependency on "php"  has to be removed';
            $phpcsFile->addError($error, $stackPtr, 'PHPDependency');
        }

        if (isset($info['dependencies']) && in_array('menu', $info['dependencies'])) {
            $error = 'dependency on "menu"  has to be removed';
            $phpcsFile->addError($error, $stackPtr, 'MenuDependency');
        }

        if (isset($info['features']['taxonomy'])
            && in_array('tags', $info['features']['taxonomy'])) {
                $error = 'taxonomy "tags" property has to be removed';
                $phpcsFile->addError($error, $stackPtr, 'TagsTaxonomy');
        }

        return $end;

    }//end process()


}//end class
