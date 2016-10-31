<?php

/**
 * @file
 * File for automated Quality Assurance checks for subsites.
 *
 * Contains NextEuropa\Phing\QualityAssuranceSubsiteTask.
 */

namespace NextEuropa\Phing;

/**
 * Quality assurance task class for subsites.
 */
class QualityAssuranceSubsiteTask extends QualityAssuranceTask {
  /**
   * Function to start the quality assurance checks.
   *
   * @param array $selected
   *   Array containing the filepathnames.
   */
  public function startQa($selected) {
    // Start output buffering.
    ob_start();
    $content = '';

    foreach ($selected as $filepathname) {
      $pathinfo = pathinfo($filepathname);
      // Print header of module, feature or theme.
      echo parent::$color['magenta'] . parent::$separator['double'];
      echo $pathinfo['dirname'] . "\r\n";
      echo parent::$color['magenta'] . parent::$separator['double'];
      $this->checkCron($pathinfo);
      $this->checkGitDiffUpdateHook($pathinfo);
      $this->checkBypassCodingStandards($pathinfo);
      $this->checkTodos($pathinfo);
      if (!$this->skipPhpcs) {
        $this->checkCodingStandards($pathinfo);
      }
      echo "\r\n";

      // Get contents of output.
      $content .= str_replace(parent::$color, '', ob_get_contents());

      // Flush contents of output.
      ob_flush();
      flush();
    }

    if (is_file($this->makeFile)) {
      echo "\r\n";
      echo parent::$color['magenta'] . parent::$separator['double'];
      echo $this->makeFile . "\r\n";
      echo parent::$color['magenta'] . parent::$separator['double'];
      $this->checkGitDiffSiteMake($this->makeFile);
      $this->checkSiteMakeForPlatformDependencies($this->makeFile);

      // Get contents of output.
      $content .= str_replace(parent::$color, '', ob_get_contents());
    }

    // Write contents to file.
    //file_put_contents('report.txt', $content);

    // Stop output buffering.
    ob_end_flush();
  }

}
