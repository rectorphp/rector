<?php

declare (strict_types=1);
namespace RectorPrefix202206;

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeFuncCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([AddClosureReturnTypeRector::class, ReturnTypeFromStrictTypedPropertyRector::class, TypedPropertyFromStrictConstructorRector::class, ParamTypeFromStrictTypedPropertyRector::class, ReturnTypeFromStrictTypedCallRector::class, AddVoidReturnTypeWhereNoReturnRector::class, ReturnTypeFromReturnNewRector::class, TypedPropertyFromStrictGetterMethodReturnTypeRector::class, AddMethodCallBasedStrictParamTypeRector::class, ArrayShapeFromConstantArrayReturnRector::class, ReturnTypeFromStrictBoolReturnExprRector::class]);
    $rectorConfig->rule(ReturnTypeFromStrictNativeFuncCallRector::class);
    $rectorConfig->rule(ReturnTypeFromStrictNewArrayRector::class);
};
