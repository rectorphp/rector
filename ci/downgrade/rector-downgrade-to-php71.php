<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\DowngradeSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

require_once __DIR__ . '/configuration.php';

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SOURCE, [
        __DIR__ . '/../../src',
        __DIR__ . '/../../packages',
        __DIR__ . '/../../rules',
        __DIR__ . '/../../tests',
        __DIR__ . '/../../vendor',
    ]);

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_71);

    $parameters->set(Option::SKIP, DowngradeRectorConfig::DEPENDENCY_EXCLUDE_PATHS);

    $parameters->set(Option::SETS, [
        DowngradeSetList::PHP_80,
        DowngradeSetList::PHP_74,
        DowngradeSetList::PHP_73,
        DowngradeSetList::PHP_72,
        DowngradeSetList::PHP_71,
    ]);
};
