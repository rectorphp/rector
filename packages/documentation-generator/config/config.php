<?php

declare(strict_types=1);

use Migrify\PhpConfigPrinter\ValueObject\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::INLINE_VALUE_OBJECT_FUNC_CALL_NAME, 'Rector\SymfonyPhpConfig\inline_value_object');
    $parameters->set(Option::INLINE_VALUE_OBJECTS_FUNC_CALL_NAME, 'Rector\SymfonyPhpConfig\inline_value_objects');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\DocumentationGenerator\\', __DIR__ . '/../src');
};
