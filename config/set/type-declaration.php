<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

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
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector::class);
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector::class);
};
