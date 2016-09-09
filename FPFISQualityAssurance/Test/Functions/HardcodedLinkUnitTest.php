<?php

class FPFISQualityAssurance_Sniffs_Functions_HardcodedLink_HardcodedLinkUnitTest extends CoderSniffUnitTest
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
            57 => 1
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
          'HardcodedLink.tpl.php' => '',
        );
    }// end getTestFiles()



}//end class
