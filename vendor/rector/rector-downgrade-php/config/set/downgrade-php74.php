<?php

declare (strict_types=1);
namespace RectorPrefix202409;

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
use Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector;
use Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector;
use Rector\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector;
use Rector\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector;
use Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector;
use Rector\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector;
use Rector\DowngradePhp74\Rector\FuncCall\DowngradeProcOpenArrayCommandArgRector;
use Rector\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector;
use Rector\DowngradePhp74\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector;
use Rector\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector;
use Rector\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector;
use Rector\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector;
use Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_73);
    $rectorConfig->rules([DowngradeTypedPropertyRector::class, ArrowFunctionToAnonymousFunctionRector::class, DowngradeCovariantReturnTypeRector::class, DowngradeContravariantArgumentTypeRector::class, DowngradeNullCoalescingOperatorRector::class, DowngradeNumericLiteralSeparatorRector::class, DowngradeStripTagsCallWithArrayRector::class, DowngradeArraySpreadRector::class, DowngradeArrayMergeCallWithoutArgumentsRector::class, DowngradeFreadFwriteFalsyToNegationRector::class, DowngradePreviouslyImplementedInterfaceRector::class, DowngradeReflectionGetTypeRector::class, DowngradeProcOpenArrayCommandArgRector::class]);
};
