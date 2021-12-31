<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector;
use Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector;
use Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector;
use Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES, \Rector\Core\ValueObject\PhpVersion::PHP_80);
    $services = $containerConfigurator->services();
    $services->set(\Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector::class);
};
