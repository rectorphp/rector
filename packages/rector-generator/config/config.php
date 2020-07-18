<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\RectorGenerator\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Exception/*', __DIR__ . '/../src/ValueObject/*']);

    $parameters = $containerConfigurator->parameters();

    $parameters->set('rector_recipe', []);
};
