<?php

declare (strict_types=1);
namespace RectorPrefix202407;

use Rector\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\Instanceof_\Rector\Ternary\FlipNegatedTernaryInstanceofRector;
use Rector\TypeDeclaration\Rector\BooleanAnd\BinaryOpNullableToInstanceofRector;
use Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;
use Rector\TypeDeclaration\Rector\While_\WhileNullableToInstanceofRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([EmptyOnNullableObjectToInstanceOfRector::class, InlineIsAInstanceOfRector::class, FlipTypeControlToUseExclusiveTypeRector::class, RemoveDeadInstanceOfRector::class, FlipNegatedTernaryInstanceofRector::class, BinaryOpNullableToInstanceofRector::class, WhileNullableToInstanceofRector::class]);
};
