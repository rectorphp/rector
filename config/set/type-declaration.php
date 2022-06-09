<?php

declare (strict_types=1);
namespace RectorPrefix20220609;

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ParamTypeDeclarationRector::class);
    $rectorConfig->rule(ReturnTypeDeclarationRector::class);
    $rectorConfig->rule(PropertyTypeDeclarationRector::class);
    $rectorConfig->rule(AddClosureReturnTypeRector::class);
    $rectorConfig->rule(AddArrayParamDocTypeRector::class);
    $rectorConfig->rule(AddArrayReturnDocTypeRector::class);
    $rectorConfig->rule(ParamTypeByParentCallTypeRector::class);
    $rectorConfig->rule(ParamTypeByMethodCallTypeRector::class);
    $rectorConfig->rule(TypedPropertyFromAssignsRector::class);
    $rectorConfig->rule(ReturnAnnotationIncorrectNullableRector::class);
    $rectorConfig->rule(VarAnnotationIncorrectNullableRector::class);
    $rectorConfig->rule(ParamAnnotationIncorrectNullableRector::class);
};
