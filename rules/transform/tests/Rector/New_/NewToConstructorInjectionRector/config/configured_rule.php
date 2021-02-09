<?php

use Rector\Transform\Rector\New_\NewToConstructorInjectionRector;
use Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NewToConstructorInjectionRector::class)->call('configure', [[
        NewToConstructorInjectionRector::TYPES_TO_CONSTRUCTOR_INJECTION => [DummyValidator::class],
    ]]);
};
