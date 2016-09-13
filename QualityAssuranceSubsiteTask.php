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
      echo SELF::COLORS['magenta'] . SELF::SEPERATOR['double'];
      echo $pathinfo['dirname'] . PHP_EOL;
      echo SELF::COLORS['magenta'] . SELF::SEPERATOR['double'];
      $this->checkCron($pathinfo);
      $this->checkGitDiffUpdateHook($pathinfo);
      $this->checkBypassCodingStandards($pathinfo);
      $this->checkTodos($pathinfo);
      if (!$this->skipPHPCS) {
        $this->checkCodingStandards($pathinfo);
      }
      echo PHP_EOL;

      // Get contents of output.
      $content .= str_replace(SELF::COLORS, '', ob_get_contents());

      // Flush contents of output.
      ob_flush();
      flush();
    }

    if (is_file($this->makeFile)) {
      echo PHP_EOL;
      echo SELF::COLORS['magenta'] . SELF::SEPERATOR['double'];
      echo $this->makeFile . PHP_EOL;
      echo SELF::COLORS['magenta'] . SELF::SEPERATOR['double'];
      $this->checkGitDiffSiteMake($this->makeFile);

      // Get contents of output.
      $content .= str_replace(SELF::COLORS, '', ob_get_contents());
    }

    // Write contents to file.
    file_put_contents('report.txt', $content);

    // Stop output buffering.
    ob_end_flush();
  }

}
