<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('symfony_container_xml_path', '');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\Symfony3\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Rector', __DIR__ . '/../src/Exception', __DIR__ . '/../src/ValueObject']);
};
