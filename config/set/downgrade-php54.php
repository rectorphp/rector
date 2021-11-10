<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector;
use Rector\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector;
use Rector\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_53);

    $services = $containerConfigurator->services();
    $services->set(ShortArrayToLongArrayRector::class);
    $services->set(DowngradeStaticClosureRector::class);
    $services->set(DowngradeBinaryNotationRector::class);
};
