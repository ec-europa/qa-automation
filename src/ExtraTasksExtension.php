<?php

declare(strict_types=1);

namespace EcEuropa\QaAutomation;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Extension that allows to define extra tasks on local grumphp.yml.dist.
 */
class ExtraTasksExtension implements ExtensionInterface
{


    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container): void
    {
        if ($container->hasParameter('extra_tasks')) {
            $tasks = $container->getParameter('tasks');
            foreach ($container->getParameter('extra_tasks') as $name => $value) {
                if (array_key_exists($name, $tasks)) {
                    throw new RuntimeException("Cannot override already defined task '{$name}' in 'extra_tasks'.");
                }

                $tasks[$name] = $value;
            }

            $container->setParameter('tasks', $tasks);
        }

    }//end load()


}//end class
