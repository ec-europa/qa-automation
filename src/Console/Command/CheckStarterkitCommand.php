<?php

/**
 * @file
 * Contains QualityAssurance\Component\Console\Command\CheckStarterkitCommand.
 */
namespace QualityAssurance\Component\Console\Command;

use GitWrapper\GitCommand;
use GitWrapper\GitException;
use GitWrapper\GitWrapper;
use QualityAssurance\Component\Console\Helper\PhingPropertiesHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CheckStarterkitCommand
 * @package QualityAssurance\Component\Console\Command
 */
class CheckStarterkitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('check:ssk')
            ->setDescription('Check if the starterkit is up to date.')
            ->addOption('branch', null, InputOption::VALUE_OPTIONAL, 'Starterkit branch.')
            ->addOption('remote', null, InputOption::VALUE_OPTIONAL, 'Starterkit remote.')
            ->addOption('repository', null, InputOption::VALUE_OPTIONAL, 'Starterkit repository on github.')
            ->addOption('project.basedir', null, InputOption::VALUE_OPTIONAL, 'Project base directory.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get the symfony console styleguide.
        $io = new SymfonyStyle($input, $output);

        // If a single option is missing load the build properties.
        if (empty($input->getOption('branch'))
          || empty($input->getOption('remote'))
          || empty($input->getOption('repository'))
          || empty($input->getOption('project.basedir'))
        ) {
            // Get the needed options for if the call came from console and not from phing.
            $phingPropertiesHelper = new PhingPropertiesHelper($output);
            $options = $phingPropertiesHelper->requestSettings(array(
              'branch' => 'starterkit.branch',
              'remote' => 'starterkit.remote',
              'repository' => 'starterkit.repository',
              'project.basedir' => 'project.basedir',
            ));
        }

        // Prepare option variables for future usage.
        $branch = !empty($input->getOption('branch')) ? $input->getOption('branch') : $options['branch'];
        $remote = !empty($input->getOption('remote')) ? $input->getOption('remote') : $options['remote'];
        // @codingStandardsIgnoreLine
        $repository = !empty($input->getOption('repository')) ? $input->getOption('repository') : $options['repository'];
        $basedir = !empty($input->getOption('project.basedir')) ? $input->getOption('project.basedir') : $options['project.basedir'];

        $subsiteRepository = $this->getGitWrapper()->workingCopy($basedir);
        // Add the remote for the starterkit if it doesn't exist yet.
        $remote_branch = 'remotes/' . $remote . '/' . $branch;
        $remote_exists = $subsiteRepository->hasRemote($remote);
        if (!$remote_exists) {
            $output->writeln("<comment>Adding remote repository.</comment>");
            // $log('Adding remote repository.');
            // Only track the given branch, and don't download any tags.
            $options = [
              '--no-tags' => true,
              '-t' => [$branch],
            ];
            $subsiteRepository->addRemote($remote, $repository, $options);
        }

        // Check if the tracking branch exists and create it if it doesn't.
        try {
            $subsiteRepository->run(array('rev-parse', $remote_branch));
        } catch (GitException $e) {
            // $log('Adding tracking branch.');
            $subsiteRepository->remote('set-branches', '--add', $remote, $branch);
        }

        // Fetch the latest changes.
        // $log('Fetching latest changes.');
        $subsiteRepository->fetch($remote);

        // Check if the latest commit on the remote is merged into the current
        // branch.
        $subsiteRepository->clearOutput();
        $latest_commit = (string) $subsiteRepository->run(array('rev-parse', $remote_branch));
        $merge_base = (string) $subsiteRepository->run(array('merge-base @ ' . $remote_branch));

        // If the latest commit on the remote is not merged into the current branch,
        // the repository is not up-to-date.
        if ($merge_base !== $latest_commit) {
            $output->writeln('');
            $repositoryUrl = 'git://github.com/ec-europa/subsite-starterkit.git';
            $request_tags = $subsiteRepository->run(array('ls-remote', '--tags', $repositoryUrl));
            $tags = array_filter(explode("\n", $request_tags->getOutput()));
            $last_tag = array_pop($tags);
            preg_match('/([0-9a-f]{5,40}).*?(starterkit\/\d+\.\d+\.[\*|\d+])$/', $last_tag, $release);
            if ($release) {
                $release_commit = $release[1];
                $release_tag = $release[2];
            }
            // Inform user on failure and give solution.
            // @codingStandardsIgnoreStart
            $output->writeln(array(
              "<comment>  Your current branch is not up to date with the latest starterkit release: $release_tag. </comment>",
              "<comment>  To update your starterkit please execute the following commands and solve any conflicts:    </comment>",
              "",
              "<comment>></comment> <info>git remote add starterkit https://github.com/ec-europa/subsite-starterkit.git</info>",
              "<comment>></comment> <info>git fetch starterkit</info>",
              "<comment>></comment> <info>git merge starterkit/master</info>",
              "",
              "<comment>  Do not forget to install the latest composer packages.</comment>",
              "",
              "<comment>></comment> <info>composer install</info>",
              "",
              "<comment>  Please verify that your site.make file has not been altered or renamed by the merge.</comment>",
              "",
            ));
            // @codingStandardsIgnoreEnd
            // Exit with error code.
            return 1;
        } else {
            // Inform user he or she is up to date.
            $output->writeln('<info>The starterkit is up to date.</info>');
        }
    }

    /**
     * Returns the GitWrapper singleton.
     *
     * @return \GitWrapper\GitWrapper
     *   The git wrapper.
     */
    protected function getGitWrapper()
    {
        if (empty($this->gitWrapper)) {
            $this->gitWrapper = new GitWrapper();
        }
        return $this->gitWrapper;
    }
}
