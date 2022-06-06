<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersion;
use RectorPrefix20220606\Rector\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector;
use RectorPrefix20220606\Rector\DowngradePhp71\Rector\ClassConst\DowngradeClassConstantVisibilityRector;
use RectorPrefix20220606\Rector\DowngradePhp71\Rector\ConstFetch\DowngradePhp71JsonConstRector;
use RectorPrefix20220606\Rector\DowngradePhp71\Rector\FuncCall\DowngradeIsIterableRector;
use RectorPrefix20220606\Rector\DowngradePhp71\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector;
use RectorPrefix20220606\Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector;
use RectorPrefix20220606\Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector;
use RectorPrefix20220606\Rector\DowngradePhp71\Rector\List_\DowngradeKeysInListRector;
use RectorPrefix20220606\Rector\DowngradePhp71\Rector\StaticCall\DowngradeClosureFromCallableRector;
use RectorPrefix20220606\Rector\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector;
use RectorPrefix20220606\Rector\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_70);
    $rectorConfig->rule(DowngradeNullableTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeVoidTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeClassConstantVisibilityRector::class);
    $rectorConfig->rule(DowngradePipeToMultiCatchExceptionRector::class);
    $rectorConfig->rule(SymmetricArrayDestructuringToListRector::class);
    $rectorConfig->rule(DowngradeNegativeStringOffsetToStrlenRector::class);
    $rectorConfig->rule(DowngradeKeysInListRector::class);
    $rectorConfig->rule(DowngradeIterablePseudoTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeIsIterableRector::class);
    $rectorConfig->rule(DowngradeClosureFromCallableRector::class);
    $rectorConfig->rule(DowngradePhp71JsonConstRector::class);
};
