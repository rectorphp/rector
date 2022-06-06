<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector;
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
