<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector;
use Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector;
use Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector;
use Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_80);
    $services = $rectorConfig->services();
    $services->set(\Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector::class);
    $services->set(\Rector\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector::class);
};
