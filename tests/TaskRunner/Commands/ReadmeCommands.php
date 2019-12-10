<?php

declare(strict_types = 1);

namespace EcEuropa\QaAutomation\Tests\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;

/**
 * Class ReadmeCommands.
 */
class ReadmeCommands extends AbstractCommands
{

    /**
     * Comment ending the sniff block.
     *
     * @var string
     */
    protected $sniffListEnd = '<!--- End snifflist. -->';

    /**
     * Comment starting the sniff block.
     *
     * @var string
     */
    protected $sniffListStart = '<!--- Start snifflist. -->';

    /**
     * Run phpcs -e --standard=QualityAssurance and replace it in the README.md.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command qa:update-sniff-list
     */
    public function updateSniffList()
    {
        $readme = './README.md';
        $sniffListBlock = $this->getSniffListBlock();
        // Replace snifflist in README.md, if any.
        $tasks[] = $this->taskReplaceInFile($readme)
            ->regex($this->getSniffListBlockRegex())
            ->to($sniffListBlock);

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Helper function to get the sniff block regex.
     *
     * @return string
     *   The regular expression.
     */
    protected function getSniffListBlockRegex()
    {
        return '/' . preg_quote($this->sniffListStart, '/') . '.*?' . preg_quote($this->sniffListEnd, '/') . '/sm';
    }

    /**
     * Helper function to get the sniff list.
     *
     * @return string
     *   The sniff list output.
     */
    protected function getSniffListBlock()
    {
        $this->taskExec("vendor/bin/phpcs --config-set installed_paths '../../drupal/coder/coder_sniffer,../../phpcompatibility/php-compatibility,phpcs'")->run();
        $sniffList = $this->taskExec('./vendor/bin/phpcs -e --standard=QualityAssurance')->printOutput(FALSE)->run()->getMessage();
        return $this->sniffListStart . PHP_EOL . PHP_EOL . '```' . $sniffList . PHP_EOL . '```' . PHP_EOL . PHP_EOL . $this->sniffListEnd;
    }
}
