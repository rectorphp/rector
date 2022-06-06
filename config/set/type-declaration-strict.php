<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector;
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
