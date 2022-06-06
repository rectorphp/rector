<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersion;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector;
use RectorPrefix20220606\Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_73);
    $rectorConfig->rule(DowngradeTypedPropertyRector::class);
    $rectorConfig->rule(ArrowFunctionToAnonymousFunctionRector::class);
    $rectorConfig->rule(DowngradeCovariantReturnTypeRector::class);
    $rectorConfig->rule(DowngradeContravariantArgumentTypeRector::class);
    $rectorConfig->rule(DowngradeNullCoalescingOperatorRector::class);
    $rectorConfig->rule(DowngradeNumericLiteralSeparatorRector::class);
    $rectorConfig->rule(DowngradeStripTagsCallWithArrayRector::class);
    $rectorConfig->rule(DowngradeArraySpreadRector::class);
    $rectorConfig->rule(DowngradeArrayMergeCallWithoutArgumentsRector::class);
    $rectorConfig->rule(DowngradeFreadFwriteFalsyToNegationRector::class);
    $rectorConfig->rule(DowngradePreviouslyImplementedInterfaceRector::class);
    $rectorConfig->rule(DowngradeReflectionGetTypeRector::class);
};
