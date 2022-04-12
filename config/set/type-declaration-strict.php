<?php

declare(strict_types=1);

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

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(AddClosureReturnTypeRector::class);
    $services->set(ReturnTypeFromStrictTypedPropertyRector::class);
    $services->set(TypedPropertyFromStrictConstructorRector::class);
    $services->set(ParamTypeFromStrictTypedPropertyRector::class);
    $services->set(ReturnTypeFromStrictTypedCallRector::class);
    $services->set(AddVoidReturnTypeWhereNoReturnRector::class);
    $services->set(ReturnTypeFromReturnNewRector::class);
    $services->set(TypedPropertyFromStrictGetterMethodReturnTypeRector::class);
    $services->set(AddMethodCallBasedStrictParamTypeRector::class);
    $services->set(ArrayShapeFromConstantArrayReturnRector::class);
};
