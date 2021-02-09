<?php

use Rector\Core\Configuration\Option;

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__ . '/../xml/services.xml');

    $services = $containerConfigurator->services();
    $services->set(\Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector::class)->call('configure', [[
        \Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector::GET_METHOD_AWARE_TYPES => [
            \Rector\Symfony\Tests\Rector\Source\SymfonyController::class,
            \Rector\Symfony\Tests\Rector\MethodCall\GetToConstructorInjectionRector\Source\GetTrait::class,
        ],
    ]]);
};
