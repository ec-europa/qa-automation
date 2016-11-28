<?php
/**
 * QualityAssurance_Sniffs_InfoFiles_FilesizeSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Checks features version and trows warning if version is api:1.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_InfoFiles_FilesizeSniff implements PHP_CodeSniffer_Sniff
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
        $fileName = $phpcsFile->getFilename();
        $fileExtension = strtolower(substr($fileName, -4));
        if ($fileExtension !== 'info') {
            return $end;
        }

        $filesize = filesize($fileName);
        if ((int)$filesize > 65535) {
            $error = 'The filesize of the info file (' . $filesize . 'bytes) exceeds the limit of 65535 bytes.';
            $phpcsFile->addError($error, null, 'Filesize');
        }

        return $end;

    }//end process()


}//end class
