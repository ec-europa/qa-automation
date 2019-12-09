<?php
/**
 * \EcEuropa\QaAutomation\TaskRunner\Commands\GrumphpCommands.
 */
declare(strict_types = 1);

namespace EcEuropa\QaAutomation\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use Robo\ResultData;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Class TestsCommands.
 */
class GrumphpCommands extends AbstractCommands implements FilesystemAwareInterface
{
    use NuvoleWebTasks\Config\loadTasks;
    use TaskRunnerTasks\CollectionFactory\loadTasks;
    use TaskRunnerTraits\ConfigurationTokensTrait;
    use TaskRunnerTraits\FilesystemAwareTrait;
    use \OpenEuropa\TaskRunner\Tasks\ProcessConfigFile\loadTasks;


    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__.'/../../../config/commands/grumphp.yml';
    }//end getConfigurationFile()


    /**
     * Check if the qa-convention is used in the grumphp.yml.dist file.
     *
     * @command qa:check-convention
     *
     * @aliases cv
     */
    public function checkConvention()
    {
        $tasks       = [];
        $grumphpFile = './grumphp.yml.dist';
        $containsQaConventions = false;

        if (file_exists($grumphpFile)) {
            $grumphpArray = (array) Yaml::parse(file_get_contents($grumphpFile));
            if (isset($grumphpArray['imports'])) {
                foreach ($grumphpArray['imports'] as $import) {
                    if (isset($import['resource']) && $import['resource'] === 'vendor/ec-europa/qa-automation/dist/qa-conventions.yml') {
                        $containsQaConventions = true;
                    }
                }
            }
        }

        if (!$containsQaConventions) {
            $this->say('All Drupal projects in the ec-europa namespace need to use Quality Assurance provided standards.');
            $this->say('Your configuration has to import the resource vendor/ec-europa/qa-automation/dist/qa-conventions.yml.');
            $this->say('Add the following lines to your grumphp.yml.dist:');
            echo "\nimports:\n  - { resource: vendor/ec-europa/qa-automation/dist/qa-conventions.yml }\n\n";
            return new ResultData(1);
        }
    }//end checkConvention()

    /**
     * Check if that config-default-path is set and ask to remove it.
     *
     * @command qa:check-config-default
     *
     * @aliases ccd
     */
    public function checkConfigDefault()
    {
        $composerFile = './composer.json';
        if (file_exists($composerFile)) {
            $composerArray = json_decode(file_get_contents($composerFile), true);
            if (isset($composerArray['extra']['grumphp']['config-default-path'])) {
                $configDefaultPath = $composerArray['extra']['grumphp']['config-default-path'];
                $this->say('You should remove the following from your composer.json extra array:');
                echo "\n\"grumphp\": {\n    \"config-default-path\": \"$configDefaultPath\"\n}\n\n";
            }
        }
    }//end checkConvention()
}//end class
