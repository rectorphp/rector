<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ParamTypeDeclarationRector::class);
    $services->set(ReturnTypeDeclarationRector::class);
    $services->set(PropertyTypeDeclarationRector::class);
    $services->set(AddClosureReturnTypeRector::class);
    $services->set(AddArrayParamDocTypeRector::class);
    $services->set(AddArrayReturnDocTypeRector::class);
    $services->set(ParamTypeByParentCallTypeRector::class);
    $services->set(ParamTypeByMethodCallTypeRector::class);
};
