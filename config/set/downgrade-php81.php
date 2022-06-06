<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersion;
use RectorPrefix20220606\Rector\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector;
use RectorPrefix20220606\Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector;
use RectorPrefix20220606\Rector\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector;
use RectorPrefix20220606\Rector\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector;
use RectorPrefix20220606\Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector;
use RectorPrefix20220606\Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector;
use RectorPrefix20220606\Rector\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector;
use RectorPrefix20220606\Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector;
use RectorPrefix20220606\Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector;
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
};
