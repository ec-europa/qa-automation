<?php

/**
 * File for automated Quality Assurance checks for subsites.
 *
 * @file
 * Contains NextEuropa\Phing\QualityAssuranceSubsiteTask.
 */

namespace NextEuropa\Phing;

/**
 * Quality assurance task class for subsites.
 */
class QualityAssuranceSubsiteTask extends QualityAssuranceTask
{
    /**
     * Function to start the quality assurance checks.
     *
     * @param array $selected Array containing the filepathnames.
     *
     * @return void
     */
    public function startQa($selected)
    {
        foreach ($selected as $filepathname) {
            $pathinfo = pathinfo($filepathname);
            // Print header of module, feature or theme.
            echo SELF::COLORS['magenta'] . SELF::SEPERATOR['double'];
            echo $pathinfo['dirname'] . "\n";
            echo SELF::COLORS['magenta'] . SELF::SEPERATOR['double'];
            $this->checkCron($pathinfo);
            $this->checkGitDiffUpdateHook($pathinfo);
            $this->checkBypassCodingStandards($pathinfo);
            $this->checkTodos($pathinfo);
            $this->checkCodingStandards($pathinfo);
            echo "\n";
        }
        echo "\n";
        echo SELF::COLORS['magenta'] . SELF::SEPERATOR['double'];
        echo $this->makeFile . "\n";
        echo SELF::COLORS['magenta'] . SELF::SEPERATOR['double'];
        $this->checkGitDiffSiteMake($this->makeFile . '.example');
    }
}
