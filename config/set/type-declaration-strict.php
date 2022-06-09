<?php

declare (strict_types=1);
namespace RectorPrefix20220609;

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(AddClosureReturnTypeRector::class);
    $rectorConfig->rule(ReturnTypeFromStrictTypedPropertyRector::class);
    $rectorConfig->rule(TypedPropertyFromStrictConstructorRector::class);
    $rectorConfig->rule(ParamTypeFromStrictTypedPropertyRector::class);
    $rectorConfig->rule(ReturnTypeFromStrictTypedCallRector::class);
    $rectorConfig->rule(AddVoidReturnTypeWhereNoReturnRector::class);
    $rectorConfig->rule(ReturnTypeFromReturnNewRector::class);
    $rectorConfig->rule(TypedPropertyFromStrictGetterMethodReturnTypeRector::class);
    $rectorConfig->rule(AddMethodCallBasedStrictParamTypeRector::class);
    $rectorConfig->rule(ArrayShapeFromConstantArrayReturnRector::class);
};
