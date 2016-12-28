<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace QualityAssurance\Component\Console\Output;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class ConsoleMessagesOutput extends ConsoleOutput
{
  public function setMessages($messages) {
    $this->messages = $messages;
  }
  public function getMessages() {
    return $this->messages;
  }
  
}