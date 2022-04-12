<?php

declare(strict_types=1);

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

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_70);

    $services = $rectorConfig->services();
    $services->set(DowngradeNullableTypeDeclarationRector::class);
    $services->set(DowngradeVoidTypeDeclarationRector::class);
    $services->set(DowngradeClassConstantVisibilityRector::class);
    $services->set(DowngradePipeToMultiCatchExceptionRector::class);
    $services->set(SymmetricArrayDestructuringToListRector::class);
    $services->set(DowngradeNegativeStringOffsetToStrlenRector::class);
    $services->set(DowngradeKeysInListRector::class);
    $services->set(DowngradeIterablePseudoTypeDeclarationRector::class);
    $services->set(DowngradeIsIterableRector::class);
    $services->set(DowngradeClosureFromCallableRector::class);
    $services->set(DowngradePhp71JsonConstRector::class);
};
