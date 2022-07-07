<?php
/**
 * \QualityAssurance\Sniffs\Functions\DrupalFormAlterSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * \QualityAssurance\Sniffs\Functions\DrupalFormAlterSniff.
 *
 * Checks the presence of form_alter function on .module files.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class DrupalForbiddenHooksSniff implements Sniff
{

    /**
     * A list of forbidden functions with their alternatives.
     *
     * The value is NULL if no alternative exists, i.e., the function should
     * just not be used.
     *
     * @var array|null
     */
    public $forbiddenHooks = [
        'hook_form_alter' => 'hook_form_FORM_ID_alter() or hook_form_BASE_FORM_ID_alter()',
    ];


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_FUNCTION];

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
        $fileName      = basename($phpcsFile->getFilename());
        $fileExtension = explode('.', $fileName);
        $fileExtension = end($fileExtension);
        if (false === in_array($fileExtension, ['module', 'theme', 'inc'])) {
            return;
        }

        $tokens       = $phpcsFile->getTokens();
        $functionName = $tokens[($stackPtr + 2)]['content'];
        $moduleName   = substr($fileName, 0, -(strlen($fileExtension) + 1));

        foreach ($this->forbiddenHooks as $hook => $replacement) {
            if (true === ($functionName === str_replace('hook', $moduleName, $hook))) {
                $warning = 'The usage of the hook %s() is forbidden';
                $data = [$hook];
                if (!empty($replacement)) {
                    $warning .= ', instead use %s.';
                    $data[] = $replacement;
                } else {
                    $warning .= '.';
                }

                $phpcsFile->addError($warning, $stackPtr, 'ForbiddenHook', $data);
            }
        }

    }//end process()


}//end class
