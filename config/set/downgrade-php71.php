<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector;
use Rector\DowngradePhp71\Rector\ClassConst\DowngradeClassConstantVisibilityRector;
use Rector\DowngradePhp71\Rector\ConstFetch\DowngradePhp71JsonConstRector;
use Rector\DowngradePhp71\Rector\FuncCall\DowngradeIsIterableRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector;
use Rector\DowngradePhp71\Rector\List_\DowngradeKeysInListRector;
use Rector\DowngradePhp71\Rector\StaticCall\DowngradeClosureFromCallableRector;
use Rector\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector;
use Rector\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_70);
    $rectorConfig->rule(\Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp71\Rector\ClassConst\DowngradeClassConstantVisibilityRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp71\Rector\List_\DowngradeKeysInListRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp71\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp71\Rector\FuncCall\DowngradeIsIterableRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp71\Rector\StaticCall\DowngradeClosureFromCallableRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp71\Rector\ConstFetch\DowngradePhp71JsonConstRector::class);
};
