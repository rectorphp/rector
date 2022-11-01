<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ParamTypeDeclarationRector::class, ReturnTypeDeclarationRector::class, PropertyTypeDeclarationRector::class, AddClosureReturnTypeRector::class, AddArrowFunctionReturnTypeRector::class, AddArrayParamDocTypeRector::class, AddArrayReturnDocTypeRector::class, ParamTypeByMethodCallTypeRector::class, TypedPropertyFromAssignsRector::class, ReturnAnnotationIncorrectNullableRector::class, VarAnnotationIncorrectNullableRector::class, ParamAnnotationIncorrectNullableRector::class, AddReturnTypeDeclarationBasedOnParentClassMethodRector::class]);
};
