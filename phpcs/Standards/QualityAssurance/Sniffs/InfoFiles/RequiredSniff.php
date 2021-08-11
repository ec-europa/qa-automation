<?php
/**
 * Drupal_Sniffs_InfoFiles_RequiredSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * "php" and "multisite_version" required properties in Drupal info files. Also
 * checks the "php" minimum requirement for Multisite 2.2.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_InfoFiles_RequiredSniff implements PHP_CodeSniffer_Sniff
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

        // Exclude from check .info files from features modules.
        if (!isset($info['features']['features_api']) &&
            (!isset($info['php']) || empty($info['php']))) {
            $error = '"php" property is missing in the info file';
            $phpcsFile->addError($error, $stackPtr, 'PHP');
        }

        if (!isset($info['multisite_version']) || empty($info['multisite_version'])) {
            $error = '"multisite_version" property is missing in the info file';
            $phpcsFile->addError($error, $stackPtr, 'MultisiteVersion');
        } elseif ($info['multisite_version'] === '2.2'
            && isset($info['php']) === true
            && $info['php'] <= '5.2'
        ) {
            $error = 'Multisite version 2.2 minimal requirement is PHP 5.2';
            $ptr   = Drupal_Sniffs_InfoFiles_ClassFilesSniff::getPtr('php', $info['php'], $phpcsFile);
            $phpcsFile->addError($error, $ptr, 'MultisitePHPVersion');
        }

        return $end;
    }//end process()
}//end class
