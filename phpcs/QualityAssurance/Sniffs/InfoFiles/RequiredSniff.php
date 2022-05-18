<?php
/**
 * \QualityAssurance\Sniffs\InfoFiles\RequiredSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\InfoFiles;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * \QualityAssurance\Sniffs\InfoFiles\RequiredSniff.
 *
 * Checks the required property of php in info.yml files.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class RequiredSniff implements Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_INLINE_HTML];

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
        // Only run this sniff once per info file.
        if (preg_match('/\.info\.yml$/', $phpcsFile->getFilename()) === 1) {
            // Drupal 8 style info.yml file.
            $contents = file_get_contents($phpcsFile->getFilename());
            try {
                $info = \Symfony\Component\Yaml\Yaml::parse($contents);
            } catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
                // If the YAML is invalid we ignore this file.
                return ($phpcsFile->numTokens + 1);
            }
        } else {
            return ($phpcsFile->numTokens + 1);
        }

				if (!isset($info['name'])) {
						$error = "The key 'name' is missing in the info file";
						$phpcsFile->addError($error, $stackPtr, 'INFO');
				}
				if (!isset($info['type'])) {
						$error = "The key 'type' is missing in the info file";
						$phpcsFile->addError($error, $stackPtr, 'INFO');
				}
				if (!isset($info['core'])) {
						$error = "The key 'core' is missing in the info file";
						$phpcsFile->addError($error, $stackPtr, 'INFO');
				}

        return ($phpcsFile->numTokens + 1);

    }//end process()


}//end class
