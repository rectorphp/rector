<?php

use Rector\Core\Configuration\Option;
use Rector\Symfony\Rector\MethodCall\GetToConstructorInjectionRector;
use Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Source\GetTrait;
use Rector\Tests\Symfony\Rector\Source\SymfonyController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__ . '/../xml/services.xml');

    $services = $containerConfigurator->services();
    $services->set(GetToConstructorInjectionRector::class)
        ->call('configure', [[
            GetToConstructorInjectionRector::GET_METHOD_AWARE_TYPES => [SymfonyController::class, GetTrait::class],
        ]]);
};
