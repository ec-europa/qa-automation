<?php
/**
 * QualityAssurance_Sniffs_InfoFiles_FeaturesSniff.
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
class QualityAssurance_Sniffs_InfoFiles_FeaturesSniff implements PHP_CodeSniffer_Sniff
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

        $tokens = $phpcsFile->getTokens();

        $contents = file_get_contents($fileName);
        $info     = QualityAssurance_Sniffs_InfoFiles_HelperClass::drupalParseInfoFormat($contents);
        // Check if the feature is using the latest API.
        if (isset($info['features']['features_api'])
            && in_array('api:1', $info['features']['features_api'])
        ) {
            $warning = 'We highly recommend upgrading features to "api:2"';
            $phpcsFile->addWarning($warning, $stackPtr, 'FeaturesAPI');
        }

        $parsedFilename = explode('/', $fileName);
        if (!isset($info['features']['features_api']) && in_array('features', $parsedFilename)) {
            // There is an exception, it can be a sub-module of some feature.
            $ffl = array_search('features', $parsedFilename);
            $mfl = array_search('modules', $parsedFilename);
            if (isset($mfl) && $mfl > $ffl) {
                // Found the exception **/features/**/modules/**, ignore module location.
            } else {
                $error = 'Please move this "custom" module out of the features folder.';
                $phpcsFile->addError($error, $stackPtr, 'CustomModule');
            }
        }

        return $end;
    }//end process()
}//end class
