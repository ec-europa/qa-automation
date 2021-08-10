<?php
/**
 * QualityAssurance_Sniffs_Generic_ComposerLockSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * \QualityAssurance\Sniffs\Generic\ComposerLockSniff.
 *
 * Checks for existence of composer.lock file.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_Generic_ComposerLockSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Internal counter to execute this sniff only once.
     *
     * @var int
     */
    private $counter = 0;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [ T_INLINE_HTML ];
    }//end register()

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // Process only one time.
        if ($this->counter > 0) {
            return;
        }

        $root = getcwd();
        // Check if the current file is the root composer.json file.
        if (strtolower($phpcsFile->getFilename()) !== strtolower($root) . '/composer.json') {
            return;
        }

        if (!file_exists($root . '/composer.lock')) {
            $this->counter++;
            $phpcsFile->addError('Composer lock file is required.', 0);
        }
    }//end process()

}//end class
