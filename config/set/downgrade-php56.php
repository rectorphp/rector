<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_55);

    $services = $containerConfigurator->services();
    $services->set(DowngradeArgumentUnpackingRector::class);
    $services->set(DowngradeExponentialAssignmentOperatorRector::class);
    $services->set(DowngradeExponentialOperatorRector::class);
};
