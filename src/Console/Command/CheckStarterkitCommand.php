<?php

namespace QualityAssurance\Component\Console\Command;

use GitWrapper\GitException;
use GitWrapper\GitWrapper;
use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckStarterkitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('check-starterkit')
            ->setDescription('Check if the starterkit is up to date.')
            ->addOption('starterkit.branch', null, InputOption::VALUE_REQUIRED, 'Starterkit branch.')
            ->addOption('starterkit.remote', null, InputOption::VALUE_REQUIRED, 'Starterkit remote.')
            ->addOption('starterkit.repository', null, InputOption::VALUE_REQUIRED, 'Starterkit repository on github.')
            ->addOption('project.basedir', null, InputOption::VALUE_REQUIRED, 'Project base directory.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get the symfony console styleguide.
        $io = new SymfonyStyle($input, $output);

        // Get the needed options for if the call came from console and not from phing.
        $phingPropertiesHelper = new PhingPropertiesHelper($input, $output);
        $options = $phingPropertiesHelper->requestRequiredSettings($input->getOptions());

        // Prepare option variables for future usage.
        $starterkitBranch = !empty($input->getOption('starterkit.branch')) ? $input->getOption('starterkit.branch') : $options['starterkit.branch'];
        $starterkitRemote = !empty($input->getOption('starterkit.remote')) ? $input->getOption('starterkit.remote') : $options['starterkit.remote'];
        $starterkitRepository = !empty($input->getOption('starterkit.repository')) ? $input->getOption('starterkit.repository') : $options['starterkit.repository'];
        $projectBasedir = !empty($input->getOption('project.basedir')) ? $input->getOption('project.basedir') : $options['project.basedir'];

        $subsiteRepository = $this->getGitWrapper()->workingCopy($projectBasedir);
        // Add the remote for the starterkit if it doesn't exist yet.
        $remote_branch = 'remotes/' . $starterkitRemote . '/' . $starterkitBranch;
        $remote_exists = $subsiteRepository->hasRemote($starterkitRemote);
        if (!$remote_exists) {
            $io->note('Adding remote repository.');
            // $log('Adding remote repository.');
            // Only track the given branch, and don't download any tags.
            $options = [
              '--no-tags' => TRUE,
              '-t' => [$starterkitBranch],
            ];
            $subsiteRepository->addRemote($starterkitRemote, $starterkitRepository, $options);
        }

        // Check if the tracking branch exists and create it if it doesn't.
        try {
            $subsiteRepository->run(array('rev-parse', $remote_branch));
        }
        catch (GitException $e) {
            // $log('Adding tracking branch.');
            $subsiteRepository->remote('set-branches', '--add', $starterkitRemote, $starterkitBranch);
        }

        // Fetch the latest changes.
        // $log('Fetching latest changes.');
        $subsiteRepository->fetch($starterkitRemote);

        // Check if the latest commit on the remote is merged into the current
        // branch.
        $subsiteRepository->clearOutput();
        $latest_commit = (string) $subsiteRepository->run(array('rev-parse', $remote_branch));
        $merge_base = (string) $subsiteRepository->run(array('merge-base @ ' . $remote_branch));

        // If the latest commit on the remote is not merged into the current branch,
        // the repository is not up-to-date.
        if ($merge_base !== $latest_commit) {
            $output->writeln('');
            $helperquestion = $this->getHelper('question');
            $question = new ChoiceQuestion("<error>Your current branch is not up to date with the starterkit.</error>\nDo you want to try to update your starterkit?", array('yes', 'no'), 1);
            $question->setErrorMessage('Please answer yes or no.');

            $selection = $helperquestion->ask($input, $output, $question);
            if ($selection == 'yes') {

            }
            else {
                throw new \BuildException('The current branch is not up to date with the starterkit.');
            }
        }
        else {
            $output->writeln('<info>The starterkit is up to date.</info>');
        }
    }

    /**
     * Returns the GitWrapper singleton.
     *
     * @return \GitWrapper\GitWrapper
     *   The git wrapper.
     */
    protected function getGitWrapper() {
        if (empty($this->gitWrapper)) {
            $this->gitWrapper = new GitWrapper();
        }
        return $this->gitWrapper;
    }
}
