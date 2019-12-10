<?php

declare(strict_types = 1);

namespace EcEuropa\QaAutomation\Tests\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;

/**
 * Class GitHookCommands.
 */
class GitHookCommands extends AbstractCommands
{

    /**
     * Add necessary lines to the git hooks.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command qa:update-git-hooks
     */
    public function updateGitHooks()
    {
        $tasks = [];
        $hooks = ['pre-commit'];
        
        foreach($hooks as $hook) {
            $tasks += $this->getAppendingTasks($hook);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * Helper function to retrieve appending tasks.
     *
     * @param string $hook
     *   The hook for which to get the appending tasks.
     *
     * @return array
     *   The array of tasks.
     */
    protected function getAppendingTasks($hook)
    {
        $tasks = [];
        $hookFilePath = "./.git/hooks/$hook";
        if (file_exists($hookFilePath)) {
            $hookFileContents = file_get_contents($hookFilePath);
            $hookLines = $this->getHookLines($hook);
            foreach ($hookLines as $hookLine) {
                if (!preg_match('/' . preg_quote($hookLine, '/') . '/', $hookFileContents)) {
                    $tasks[] = $this->taskWriteToFile($hookFilePath)
                       ->append()
                       ->line($hookLine);
                }
            }
        }
        return $tasks;
    }

    /**
     * Helper function to get the hook lines.
     *
     * @param string $hook
     *   The hook for which to get the hook lines.
     *
     * @return array
     *   The array of lines to put in the hook.
     */
    protected function getHookLines($hook)
    {
        $hookLines = [
            'pre-commit' => [
                './vendor/bin/run qa:update-sniff-list',
            ],
        ];
        
        return in_array($hook, $hookLines) ? $hookLines[$hook] : [];
    }
}
