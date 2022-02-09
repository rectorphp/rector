<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector;
use Rector\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector;
use Rector\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector;
use Rector\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector;
use Rector\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector;
use Rector\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES, \Rector\Core\ValueObject\PhpVersion::PHP_53);
    $services = $containerConfigurator->services();
    $services->set(\Rector\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector::class);
    $services->set(\Rector\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector::class);
    $services->set(\Rector\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector::class);
    $services->set(\Rector\DowngradePhp54\Rector\FunctionLike\DowngradeCallableTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector::class);
    $services->set(\Rector\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector::class);
};
