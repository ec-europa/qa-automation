<?php

class QualityAssurance_Sniffs_Functions_DrupalJsonDecode_DrupalJsonDecodeUnitTest extends CoderSniffUnitTest
{
  /**
   * Returns the lines where errors should occur.
   *
   * The key of the array should represent the line number and the value
   * should represent the number of errors that should occur on that line.
   *
   * @return array(int => int)
   */
  public function getErrorList($testFile)
  {
    // All the Error files have  errors.
    $errors = [
      15 => 1,
      16 => 1,
      17 => 1,
      18 => 1,
    ];
    return (strpos($testFile, 'Error') === false) ? [] : $errors;
  }

  /**
   * Returns the lines where warnings should occur.
   *
   * The key of the array should represent the line number and the value
   * should represent the number of warnings that should occur on that line.
   *
   * @return array(int => int)
   */
  public function getWarningList($testFile)
  {
    // All the Warming files have  warnings.
    return (strpos($testFile, 'Warning') === false) ? [] : [1 => 1];
  }

  /**
   * Returns a list of test files that should be checked.
   *
   * The key of the array should represent the file that should be tested.
   * The value of the array represents the fixed file to compare against.
   *
   * @return array('fileToTest" => 'fixedFile')
   */
  public function getTestFiles() {
    return array(
      'error/DrupalJsonDecodeError.inc' => '',
    );
  }

}
