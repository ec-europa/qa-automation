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
use Symfony\Component\Yaml\Exception\ParseException;
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
        return [ T_INLINE_HTML ];

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
        // Only check the docker-compose.yml file.
        if (strtolower(substr($filePath, -18)) !== 'docker-compose.yml') {
            return;
        }

        $fileContent  = file($filePath);
        $checkEnvVars = [
            'asda_user',
            'asda_pass',
            'api_token',
        ];
        try {
            $yaml = Yaml::parseFile($filePath);
        } catch (ParseException $e) {
            $phpcsFile->addError($e->getMessage(), $stackPtr, 'Yaml');
            return ($phpcsFile->numTokens + 1);
        }

        // Parse the environment variables.
        if (isset($yaml['services']) === true) {
            foreach ($yaml['services'] as $service) {
                // Check if environment variables contain credentials.
                if (isset($service['environment']) === true) {
                    foreach ($service['environment'] as $envVarName => $envVarValue) {
                        foreach ($checkEnvVars as $checkEnvVar) {
                            $envVarNameLower = strtolower($envVarName);
                            if (strpos($envVarNameLower, $checkEnvVar) !== false && $envVarValue !== '') {
                                $lines   = preg_grep("/($envVarName)/s", $fileContent);
                                $message = "Do not commit credentials in the docker-compose.yml file! $envVarName has a value. It should remain empty.";
                                $phpcsFile->addError($message, array_key_first($lines), 'Credentials');
                            }
                        }
                    }
                }

                // Check if an env_file is used and check that also for
                // credentials.
                if (isset($service['env_file']) === true) {
                    foreach ($service['env_file'] as $envFile) {
                        $envFilePath = dirname($filePath).'/'.$envFile;
                        if (file_exists($envFilePath) === true) {
                            $fileContent = file($envFilePath);
                            foreach ($checkEnvVars as $checkEnvVar) {
                                $lines = preg_grep("/(.*?$checkEnvVar.*?)=(.*?)\\n/si", $fileContent);
                                if (empty($lines) === false) {
                                    list($key, $value) = explode('=', reset($lines), 2);
                                    if (empty($key) === false) {
                                        $message = "Do not commit credentials in the $envFile file! $key has a value. It should remain empty.";
                                        $phpcsFile->addError($message, array_key_first($lines), 'Credentials');
                                    }
                                }
                            }
                        }
                    }
                }
            }//end foreach
        }//end if

        // Only run this sniff once on the file.
        return ($phpcsFile->numTokens + 1);

    }//end process()


}//end class
