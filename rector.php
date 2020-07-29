<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Set\ValueObject\SetList;
use Rector\SymfonyPhpConfig\Rector\MethodCall\ChangeCallByNameToConstantByClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/create-rector.php', null, 'not_found');

    $services = $containerConfigurator->services();

    $services->set(ChangeCallByNameToConstantByClassRector::class)
        ->call('configure', [
            [
                ChangeCallByNameToConstantByClassRector::CLASS_TYPES_TO_METHOD_NAME => [
                    RectorInterface::class => 'configure',
                ],
            ],
        ]);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::SETS, [SetList::NAMING]);

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
        '*.php.inc',
    ]);

    # so Rector code is still PHP 7.2 compatible
    $parameters->set(Option::PHP_VERSION_FEATURES, '7.2');
};
