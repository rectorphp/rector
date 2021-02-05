<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                // interop deprecated interop to psr
                'Interop\Container\ContainerInterface' => 'Psr\Container\ContainerInterface',

                // update deprecated root factory to new factory under new namespace
                'Laminas\ServiceManager\AbstractFactoryInterface' => 'Laminas\ServiceManager\Factory\AbstractFactoryInterface',
                'Laminas\ServiceManager\FactoryInterface' => 'Laminas\ServiceManager\Factory\FactoryInterface',
                'Laminas\ServiceManager\DelegatorFactoryInterface' => 'Laminas\ServiceManager\Factory\DelegatorFactoryInterface',
                'Laminas\ServiceManager\InitializerInterface' => 'Laminas\ServiceManager\Initializer\InitializerInterface',

                // update deprecated root factory to new factory under new namespace for laminas/laminas-zendframework-bridge
                'Zend\ServiceManager\AbstractFactoryInterface' => 'Laminas\ServiceManager\Factory\AbstractFactoryInterface',
                'Zend\ServiceManager\FactoryInterface' => 'Laminas\ServiceManager\Factory\FactoryInterface',
                'Zend\ServiceManager\DelegatorFactoryInterface' => 'Laminas\ServiceManager\Factory\DelegatorFactoryInterface',
                'Zend\ServiceManager\InitializerInterface' => 'Laminas\ServiceManager\Initializer\InitializerInterface',
            ],
        ]]);
};
