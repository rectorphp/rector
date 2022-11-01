<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector;
use Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeEnumExistsRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector;
use Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector;
use Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_80);
    $rectorConfig->rule(DowngradeFinalizePublicClassConstantRector::class);
    $rectorConfig->rule(DowngradeFirstClassCallableSyntaxRector::class);
    $rectorConfig->rule(DowngradeNeverTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradePureIntersectionTypeRector::class);
    $rectorConfig->rule(DowngradeNewInInitializerRector::class);
    $rectorConfig->rule(DowngradePhp81ResourceReturnToObjectRector::class);
    $rectorConfig->rule(DowngradeReadonlyPropertyRector::class);
    $rectorConfig->rule(DowngradeArraySpreadStringKeyRector::class);
    $rectorConfig->rule(DowngradeArrayIsListRector::class);
    $rectorConfig->rule(DowngradeEnumExistsRector::class);
};
