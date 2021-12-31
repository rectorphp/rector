<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector;
use Rector\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector;
use Rector\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES, \Rector\Core\ValueObject\PhpVersion::PHP_55);
    $services = $containerConfigurator->services();
    $services->set(\Rector\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector::class);
    $services->set(\Rector\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector::class);
    $services->set(\Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector::class);
    $services->set(\Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector::class);
    $services->set(\Rector\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector::class);
};
