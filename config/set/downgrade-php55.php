<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector;
use Rector\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector;
use Rector\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector;
use Rector\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES, \Rector\Core\ValueObject\PhpVersion::PHP_54);
    $services = $containerConfigurator->services();
    $services->set(\Rector\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector::class);
    $services->set(\Rector\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector::class);
    $services->set(\Rector\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector::class);
    $services->set(\Rector\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector::class);
};
