<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Naming\Rector\Assign\RenameVariableToMatchGetMethodNameRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/create-rector.php', null, 'not_found');

    $services = $containerConfigurator->services();

    $services->set(RenameVariableToMatchGetMethodNameRector::class);

    $parameters = $containerConfigurator->parameters();

    #    Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector: null
    #    Rector\Autodiscovery\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector: null
    # bleeding edge feature
    # is_cache_enabled: true
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/rules', __DIR__ . '/utils', __DIR__ . '/packages']);

    $parameters->set(Option::EXCLUDE_PATHS, ['/Source/', '/*Source/', '/Fixture/', '/Expected/', __DIR__ . '/packages/doctrine-annotation-generated/src/*', '*.php.inc']);

    # so Rector code is still PHP 7.2 compatible
    $parameters->set(Option::PHP_VERSION_FEATURES, '7.2');
};
