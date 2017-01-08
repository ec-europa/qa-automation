<?php
/**
 * An abstract class that all sniff unit tests must extend.
 */

/**
 * An abstract class that all sniff unit tests must extend.
 *
 * This is a modified copy of AbstractSniffUnitTest used in PHP_CodeSniffer.
 */
abstract class CoderSniffUnitTest extends PHPUnit_Framework_TestCase
{

    /**
     * The PHP_CodeSniffer object used for testing.
     *
     * @var PHP_CodeSniffer
     */
    protected static $phpcs = null;


    /**
     * Sets up this unit test.
     *
     * @return void
     */
    protected function setUp()
    {
        if (!defined('PHP_CODESNIFFER_IN_TESTS')) {
            define('PHP_CODESNIFFER_IN_TESTS', true);
        }
        if (self::$phpcs === null) {
            self::$phpcs = new PHP_CodeSniffer(0, 0, 'utf-8');
        }

    }//end setUp()


    /**
     * Should this test be skipped for some reason.
     *
     * @return void
     */
    protected function shouldSkipTest()
    {
        return false;

    }//end shouldSkipTest()


    /**
     * Tests the extending classes Sniff class.
     *
     * @return void
     * @throws PHPUnit_Framework_Error
     */
    public final function testSniff()
    {
        // Skip this test if we can't run in this environment.
        if ($this->shouldSkipTest() === true) {
            $this->markTestSkipped();
        }

        $testFiles = $this->getTestFiles();
        $sniffCodes = $this->getSniffCodes();

        // Determine the standard to be used from the class name.
        $class_name_parts = explode('_', get_class($this));
        $standard = $class_name_parts[0];

        $failureMessages = array();
        foreach (array_keys($testFiles) as $filename) {
            self::$phpcs->setConfigData('installed_paths', __DIR__ . '/../../');
            self::$phpcs->initStandard("phpcs/Standards/$standard", $sniffCodes);
            $rc = new ReflectionClass(get_class($this));
            $testFile = dirname($rc->getFileName()) . '/' . $filename;

            try {
                $cliValues = $this->getCliValues($filename);
                self::$phpcs->cli->setCommandLineValues($cliValues);
                $phpcsFile = self::$phpcs->processFile($testFile);
            } catch (Exception $e) {
                $this->fail('An unexpected exception has been caught: '.$e->getMessage());
            }

            $failures        = $this->generateFailureMessages($phpcsFile);
            $failureMessages = array_merge($failureMessages, $failures);

            // Attempt to fix the errors.
            // Re-initialize the standard to use all sniffs for the fixer.
            self::$phpcs->initStandard("phpcs/Standards/$standard");
            self::$phpcs->cli->setCommandLineValues($cliValues);
            $phpcsFile = self::$phpcs->processFile($testFile);

            $phpcsFile->fixer->fixFile();
            $fixable = $phpcsFile->getFixableCount();
            if ($fixable > 0) {
                $failureMessages[] = "Failed to fix $fixable fixable violations in $filename";
            }

            // Check for a .fixed file to check for accuracy of fixes.
            $fixedFile = empty($testFiles[$filename]) ? '' : dirname($rc->getFileName()) . '/' . $testFiles[$filename];
            if (file_exists($fixedFile) === true) {
                $diff = $phpcsFile->fixer->generateDiff($fixedFile);
                if (trim($diff) !== '') {
                    $filename          = basename($testFile);
                    $fixedFilename     = basename($fixedFile);
                    $failureMessages[] = "Fixed version of $filename does not match expected version in $fixedFilename; the diff is\n$diff";
                }
            }
        }//end foreach

        if (empty($failureMessages) === false) {
            $this->fail(implode(PHP_EOL, $failureMessages));
        }

    }//end testSniff()

    /**
     * Returns a list of sniff codes that should be checked in this test.
     *
     * @return array The list of sniff codes.
     */
    protected function getSniffCodes() {
        // The basis for determining file locations.
        $basename = substr(get_class($this), 0, -8);

        // The code of the sniff we are testing.
        $parts     = explode('_', $basename);
        return array($parts[0].'.'.$parts[2].'.'.$parts[3]);
    }

    /**
     * Generate a list of test failures for a given sniffed file.
     *
     * @param PHP_CodeSniffer_File $file The file being tested.
     *
     * @return array
     * @throws PHP_CodeSniffer_Exception
     */
    public function generateFailureMessages(PHP_CodeSniffer_File $file)
    {
        $testFile = $file->getFilename();

        $foundErrors      = $file->getErrors();
        $foundWarnings    = $file->getWarnings();
        $expectedErrors   = $this->getErrorList(basename($testFile));
        $expectedWarnings = $this->getWarningList(basename($testFile));

        if (is_array($expectedErrors) === false) {
            throw new PHP_CodeSniffer_Exception('getErrorList() must return an array');
        }

        if (is_array($expectedWarnings) === false) {
            throw new PHP_CodeSniffer_Exception('getWarningList() must return an array');
        }

        /*
            We merge errors and warnings together to make it easier
            to iterate over them and produce the errors string. In this way,
            we can report on errors and warnings in the same line even though
            it's not really structured to allow that.
        */

        $allProblems     = array();
        $failureMessages = array();

        foreach ($foundErrors as $line => $lineErrors) {
            foreach ($lineErrors as $column => $errors) {
                if (isset($allProblems[$line]) === false) {
                    $allProblems[$line] = array(
                      'expected_errors'   => 0,
                      'expected_warnings' => 0,
                      'found_errors'      => array(),
                      'found_warnings'    => array(),
                    );
                }

                $foundErrorsTemp = array();
                foreach ($allProblems[$line]['found_errors'] as $foundError) {
                    $foundErrorsTemp[] = $foundError;
                }

                $errorsTemp = array();
                foreach ($errors as $foundError) {
                    $errorsTemp[] = $foundError['message'].' ('.$foundError['source'].')';
                }

                $allProblems[$line]['found_errors'] = array_merge($foundErrorsTemp, $errorsTemp);
            }//end foreach

            if (isset($expectedErrors[$line]) === true) {
                $allProblems[$line]['expected_errors'] = $expectedErrors[$line];
            } else {
                $allProblems[$line]['expected_errors'] = 0;
            }

            unset($expectedErrors[$line]);
        }//end foreach

        foreach ($expectedErrors as $line => $numErrors) {
            if (isset($allProblems[$line]) === false) {
                $allProblems[$line] = array(
                  'expected_errors'   => 0,
                  'expected_warnings' => 0,
                  'found_errors'      => array(),
                  'found_warnings'    => array(),
                );
            }

            $allProblems[$line]['expected_errors'] = $numErrors;
        }

        foreach ($foundWarnings as $line => $lineWarnings) {
            foreach ($lineWarnings as $column => $warnings) {
                if (isset($allProblems[$line]) === false) {
                    $allProblems[$line] = array(
                      'expected_errors'   => 0,
                      'expected_warnings' => 0,
                      'found_errors'      => array(),
                      'found_warnings'    => array(),
                    );
                }

                $foundWarningsTemp = array();
                foreach ($allProblems[$line]['found_warnings'] as $foundWarning) {
                    $foundWarningsTemp[] = $foundWarning;
                }

                $warningsTemp = array();
                foreach ($warnings as $warning) {
                    $warningsTemp[] = $warning['message'].' ('.$warning['source'].')';
                }

                $allProblems[$line]['found_warnings'] = array_merge($foundWarningsTemp, $warningsTemp);
            }//end foreach

            if (isset($expectedWarnings[$line]) === true) {
                $allProblems[$line]['expected_warnings'] = $expectedWarnings[$line];
            } else {
                $allProblems[$line]['expected_warnings'] = 0;
            }

            unset($expectedWarnings[$line]);
        }//end foreach

        foreach ($expectedWarnings as $line => $numWarnings) {
            if (isset($allProblems[$line]) === false) {
                $allProblems[$line] = array(
                  'expected_errors'   => 0,
                  'expected_warnings' => 0,
                  'found_errors'      => array(),
                  'found_warnings'    => array(),
                );
            }

            $allProblems[$line]['expected_warnings'] = $numWarnings;
        }

        // Order the messages by line number.
        ksort($allProblems);

        foreach ($allProblems as $line => $problems) {
            $numErrors        = count($problems['found_errors']);
            $numWarnings      = count($problems['found_warnings']);
            $expectedErrors   = $problems['expected_errors'];
            $expectedWarnings = $problems['expected_warnings'];


            // Uncomment the following generate line error pairs for the bad unit
            // test.
            /*if ($numErrors) {
                print "$line => " . $numErrors . ",\n";
            }
            if ($numWarnings) {
                print "$line => " . $numWarnings . ",\n";
            }*/

            $errors      = '';
            $foundString = '';

            if ($expectedErrors !== $numErrors || $expectedWarnings !== $numWarnings) {
                $lineMessage     = "[LINE $line]";
                $expectedMessage = 'Expected ';
                $foundMessage    = 'in '.basename($testFile).' but found ';

                if ($expectedErrors !== $numErrors) {
                    $expectedMessage .= "$expectedErrors error(s)";
                    $foundMessage    .= "$numErrors error(s)";
                    if ($numErrors !== 0) {
                        $foundString .= 'error(s)';
                        $errors      .= implode(PHP_EOL.' -> ', $problems['found_errors']);
                    }

                    if ($expectedWarnings !== $numWarnings) {
                        $expectedMessage .= ' and ';
                        $foundMessage    .= ' and ';
                        if ($numWarnings !== 0) {
                            if ($foundString !== '') {
                                $foundString .= ' and ';
                            }
                        }
                    }
                }

                if ($expectedWarnings !== $numWarnings) {
                    $expectedMessage .= "$expectedWarnings warning(s)";
                    $foundMessage    .= "$numWarnings warning(s)";
                    if ($numWarnings !== 0) {
                        $foundString .= 'warning(s)';
                        if (empty($errors) === false) {
                            $errors .= PHP_EOL.' -> ';
                        }

                        $errors .= implode(PHP_EOL.' -> ', $problems['found_warnings']);
                    }
                }

                $fullMessage = "$lineMessage $expectedMessage $foundMessage.";
                if ($errors !== '') {
                    $fullMessage .= " The $foundString found were:".PHP_EOL." -> $errors";
                }

                $failureMessages[] = $fullMessage;
            }//end if
        }//end foreach

        return $failureMessages;

    }//end generateFailureMessages()


    /**
     * Get a list of CLI values to set before the file is tested.
     *
     * @param string $filename The name of the file being tested.
     *
     * @return array
     */
    public function getCliValues($filename)
    {
        return array();

    }//end getCliValues()


    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array(int => int)
     */
    protected abstract function getErrorList($testFile);


    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of warnings that should occur on that line.
     *
     * @return array(int => int)
     */
    protected abstract function getWarningList($testFile);


    /**
     * Returns a list of test files that should be checked.
     *
     * The key of the array should represent the file that should be tested.
     * The value of the array represents the fixed file to compare against.
     *
     * @return array('fileToTest" => 'fixedFile')
     */
    protected abstract function getTestFiles();


}//end class
