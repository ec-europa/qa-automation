<?php
/**
 * \QualityAssurance\Sniffs\Generic\CredentialsSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\Generic;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Symfony\Component\Yaml\Yaml;

/**
 * \QualityAssurance\Sniffs\Generic\CredentialsSniff.
 *
 * Checks docker-compose.yml for credentials.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class CredentialsSniff implements Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
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
        $filePath = $phpcsFile->getFilename();
        $fileName = strtolower(substr($filePath, -18));
        // Only check the docker-compose.yml file.
        if ($fileName !== 'docker-compose.yml') {
            return;
        }
        $fileContent = file($filePath);
        $checkEnvVars = ['asda_user', 'asda_pass', 'api_token'];
        // Parse the environment variables.
        if ($yaml = Yaml::parseFile($filePath)) {
            if (isset($yaml['services'])) {
                foreach ($yaml['services'] as $service) {
                    if (isset($service['environment'])) {
                        foreach ($service['environment'] as $envVarName => $envVarValue) {
                            foreach ($checkEnvVars as $checkEnvVar) {
                                $envVarNameLower = strtolower($envVarName);
                                if (strpos($envVarNameLower, $checkEnvVar) !== FALSE && $envVarValue !== '') {
                                    $lines = preg_grep("/($envVarName)/s", $fileContent);
                                    $message = "Do not commit credentials! $envVarName has a value. It should remain empty.";
                                    $phpcsFile->addError($message, array_key_first($lines), 'Credentials');
                                }
                            }
                        }
                    }
                }
            }
        }
        // Only run this sniff once on the file.
        return ($phpcsFile->numTokens + 1);

    }//end process()


}//end class
