<?php

class QualityAssurance_Sniffs_FeaturesFiles_ForbiddenPermissions_ForbiddenPermissionsUnitTest extends CoderSniffUnitTest
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
        return array(
            15 => 1,
            24 => 1,
            33 => 0,
            40 => 1,
            49 => 0,
            56 => 0,
            63 => 1,
            72 => 0,
            79 => 0,
            86 => 1,
            95 => 1,
            104 => 1,
            113 => 1,
            122 => 1,
        );

    }//end getErrorList()


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
        return array(

        );

    }//end getWarningList()


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
          'ForbiddenPermissions.features.user_permission.inc' => '',
        );
    }// end getTestFiles()


}//end class
