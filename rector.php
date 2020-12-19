<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'A' => 'B',
            ],
        ]]);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/rules',
        __DIR__ . '/utils',
        __DIR__ . '/packages',
        __DIR__ . '/bin/rector',
    ]);

    $parameters->set(Option::SKIP, [
        '*/Source/*',
        '*/Fixture/*',
        '*/Expected/*',
        __DIR__ . '/packages/doctrine-annotation-generated/src/*',
        __DIR__ . '/packages/rector-generator/templates/*',
        '*.php.inc',
    ]);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    # so Rector code is still PHP 7.2 compatible
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_72);
};
