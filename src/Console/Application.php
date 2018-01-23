<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Application.
 */

namespace QualityAssurance\Component\Console;

use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Application
 * @package QualityAssurance\Component\Console
 */
class Application extends BaseApplication
{
  const NAME = 'QA Automation';
  const VERSION = '1.0';

  private $properties;

  public function __construct()
  {
    parent::__construct(static::NAME, static::VERSION);

    $phingPropertiesHelper = new PhingPropertiesHelper(new NullOutput());
    $properties = $phingPropertiesHelper->getAllSettings();
    $this->properties = $properties;
  }

  public function getProperties()
  {
    return $this->properties;
  }
}
