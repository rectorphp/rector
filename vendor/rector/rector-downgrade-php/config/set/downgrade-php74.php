<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
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
    $rectorConfig->rule(DowngradeProcOpenArrayCommandArgRector::class);
};
