<?php
/**
 * Ensures hooks comment on update function is correct.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Ensures hooks comment on update function is correct.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class QualityAssurance_Sniffs_InstallFiles_HookUpdateNSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_FUNCTION);
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
        $fileExtension = strtolower(substr($phpcsFile->getFilename(), -7));
        if ($fileExtension !== 'install') {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $functionName = $tokens[$stackPtr +2]['content'];
        $fileName = substr(basename($phpcsFile->getFilename()), 0, -8);

        if (!preg_match('/' . $fileName . '_update_7\d{3}$/', $functionName)) {
            return;
        }

        $find   = PHP_CodeSniffer_Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;


        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT
        ) {
            return;
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            return;
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];

        $empty = array(
                  T_DOC_COMMENT_WHITESPACE,
                  T_DOC_COMMENT_STAR,
                 );

        $short = $phpcsFile->findNext($empty, ($commentStart + 1), $commentEnd, true);
        if ($short === false) {
            // No content at all.
            return;
        }

        // Account for the fact that a short description might cover
        // multiple lines.
        $shortContent = $tokens[$short]['content'];
        $shortEnd     = $short;
        for ($i = ($short + 1); $i < $commentEnd; $i++) {
            if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING) {
                if ($tokens[$i]['line'] === ($tokens[$shortEnd]['line'] + 1)) {
                    $shortContent .= $tokens[$i]['content'];
                    $shortEnd      = $i;
                } else {
                    break;
                }
            }
        }

        // Check if hook_update_N implementation doc is formated correctly.
        if (preg_match('/^[\s]*Implement[^\n]+?hook_update_N[^\n]+/i', $shortContent, $matches)) {
            $phpcsFile->addError('Replace "' . $matches[0] . '" with a short description on the hooks functionality.', $short, "UpdateNDescription");
        }//end if
    }//end process()
}//end class
