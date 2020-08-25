<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Generic\ValueObject\RemovedArgument;
use Rector\SymfonyPhpConfig\Rector\ArrayItem\ReplaceArrayWithObjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReplaceArrayWithObjectRector::class)
        ->call('configure', [[
            ReplaceArrayWithObjectRector::CONSTANT_NAMES_TO_VALUE_OBJECTS => [
                'Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector::REMOVED_ARGUMENTS' => RemovedArgument::class,
            ],
        ]]);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/rules',
        __DIR__ . '/utils',
        __DIR__ . '/packages',
        __DIR__ . '/bin/rector',
    ]);

    $parameters->set(Option::EXCLUDE_PATHS, [
        '/Source/',
        '/*Source/',
        '/Fixture/',
        '/Expected/',
        __DIR__ . '/packages/doctrine-annotation-generated/src/*',
        __DIR__ . '/packages/rector-generator/templates/*',
        '*.php.inc',
    ]);

    # so Rector code is still PHP 7.2 compatible
    $parameters->set(Option::PHP_VERSION_FEATURES, '7.2');
};
