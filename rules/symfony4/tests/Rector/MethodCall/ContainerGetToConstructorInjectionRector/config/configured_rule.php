<?php

use Rector\Core\Configuration\Option;
use Rector\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;
use Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentCommand;
use Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source\ThisClassCallsMethodInConstructor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__ . '/../xml/services.xml');

    $services = $containerConfigurator->services();
    $services->set(ContainerGetToConstructorInjectionRector::class)
        ->call('configure', [[
            ContainerGetToConstructorInjectionRector::CONTAINER_AWARE_PARENT_TYPES => [
                ContainerAwareParentClass::class,
                ContainerAwareParentCommand::class,
                ThisClassCallsMethodInConstructor::class,
            ],
        ]]
    );
};
