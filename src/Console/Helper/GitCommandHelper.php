<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Helper\GitCommandHelper.
 */

namespace QualityAssurance\Component\Console\Helper;

use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use GitWrapper\GitWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReviewCommandHelper
 * @package QualityAssurance\Component\Console\Helper
 */
class GitCommandHelper
{
  /**
   * GitCommandHelper constructor.
   *
   * Setup our input output interfaces and other variables.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @param $application
   */
  function __construct(InputInterface $input, OutputInterface $output, $application)
  {
    // Set construct properties.
    $this->input = $input;
    $this->output = $output;
    $this->application = $application;
  }

  /**
   * Helper function to set the git wrapper object.
   *
   * @param $options
   */
  public function setGitWrapper($params)
  {
    // Get a diff of current branch and master.

    $wrapper = new GitWrapper();
    $git = $wrapper->workingCopy($params['dirname']);

    // Add starterkit remote if do not exists
    $remote_exists = $git->hasRemote($params['remote']);
    if (!$remote_exists) {
      $this->output->writeln("<comment>Adding remote to repository: </comment><info>" . $params['remote'] . "</info>");
      // Only track the given branch, and don't download any tags.
      $options = [
        '--no-tags' => TRUE,
        '-t' => [$params['branch']],
      ];
      $git->addRemote($params['remote'], $params['repository'], $options);
    }

    // Add reference remote if do not exist.
    $remote_exists = $git->hasRemote($params['reference_remote']);
    if (!$remote_exists) {
      $this->output->writeln("<comment>Adding remote to repository: </comment><info>" . $params['reference_remote'] . "</info>");
      // Only track the given branch, and don't download any tags.
      $options = [
        '--no-tags' => TRUE,
        '-t' => [$params['reference_branch']],
      ];
      $git->addRemote($params['reference_remote'], $params['reference_repository'], $options);
    }

    return $git;
  }

}
