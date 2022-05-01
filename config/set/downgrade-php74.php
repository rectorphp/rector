<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector;
use Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector;
use Rector\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector;
use Rector\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector;
use Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector;
use Rector\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector;
use Rector\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector;
use Rector\DowngradePhp74\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector;
use Rector\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector;
use Rector\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector;
use Rector\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector;
use Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_73);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector::class);
};
