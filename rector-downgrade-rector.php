<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::EXCLUDE_PATHS, [
        // '/Source/',
        // '/*Source/',
        // '/Fixture/',
        // '/Expected/',
        __DIR__ . '/packages/doctrine-annotation-generated/src/*',
        __DIR__ . '/packages/rector-generator/templates/*',
        __DIR__ . '/vendor/*',
        __DIR__ . '/ci/*',
        '/tests/',
        // '*.php.inc',
    ]);
};
