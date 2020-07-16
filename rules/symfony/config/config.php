<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('symfony_container_xml_path', '');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire();

    $services->load('Rector\Symfony\\', __DIR__ . '/../src')
        ->exclude(
            [
                __DIR__ . '/../src/Rector/**/*Rector.php',
                __DIR__ . '/../src/Exception/*',
                __DIR__ . '/../src/ValueObject/*',
                __DIR__ . '/../src/PhpDocParser/Ast/PhpDoc/*',
            ]
        );
};
