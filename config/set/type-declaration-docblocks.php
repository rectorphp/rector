<?php

declare (strict_types=1);
namespace RectorPrefix202509;

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnArrayDocblockBasedOnArrayMapRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnDocblockForScalarArrayFromAssignsRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarFromParamDocblockInConstructorRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDataProviderRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDimFetchAccessRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForCommonObjectDenominatorRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockGetterReturnArrayFromPropertyDocblockVarRector;
/**
 * @experimental * 2025-09, experimental hidden set for type declaration in docblocks
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AddReturnArrayDocblockBasedOnArrayMapRector::class, AddReturnDocblockForScalarArrayFromAssignsRector::class, DocblockVarFromParamDocblockInConstructorRector::class, DocblockVarFromParamDocblockInConstructorRector::class, DocblockGetterReturnArrayFromPropertyDocblockVarRector::class, AddReturnDocblockForCommonObjectDenominatorRector::class, AddParamArrayDocblockFromDimFetchAccessRector::class, AddParamArrayDocblockFromDataProviderRector::class]);
};
