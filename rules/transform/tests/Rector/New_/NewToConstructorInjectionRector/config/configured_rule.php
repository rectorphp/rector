<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\New_\NewToConstructorInjectionRector::class)->call('configure', [[
        \Rector\Transform\Rector\New_\NewToConstructorInjectionRector::TYPES_TO_CONSTRUCTOR_INJECTION => [
            \Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator::class,
        ],
    ]]);
};
