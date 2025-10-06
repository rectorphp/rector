<?php

declare (strict_types=1);
namespace Rector\Config\Level;

use Rector\Contract\Rector\RectorInterface;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnArrayDocblockBasedOnArrayMapRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnDocblockForScalarArrayFromAssignsRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\AddReturnDocblockDataProviderRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\ClassMethodArrayDocblockParamFromLocalCallsRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromGetterReturnRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromPropertyDefaultsRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarFromParamDocblockInConstructorRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockBasedOnArrayMapRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDataProviderRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDimFetchAccessRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForArrayDimAssignedObjectRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForCommonObjectDenominatorRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForJsonArrayRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockGetterReturnArrayFromPropertyDocblockVarRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockReturnArrayFromDirectArrayInstanceRector;
final class TypeDeclarationDocblocksLevel
{
    /**
     * @var array<class-string<RectorInterface>>
     */
    public const RULES = [
        // start with rules based on native code
        // property var
        DocblockVarArrayFromPropertyDefaultsRector::class,
        // tests
        AddParamArrayDocblockFromDataProviderRector::class,
        AddReturnDocblockDataProviderRector::class,
        // param
        AddParamArrayDocblockFromDimFetchAccessRector::class,
        ClassMethodArrayDocblockParamFromLocalCallsRector::class,
        AddParamArrayDocblockBasedOnArrayMapRector::class,
        // return
        AddReturnDocblockForCommonObjectDenominatorRector::class,
        AddReturnArrayDocblockBasedOnArrayMapRector::class,
        AddReturnDocblockForScalarArrayFromAssignsRector::class,
        DocblockReturnArrayFromDirectArrayInstanceRector::class,
        AddReturnDocblockForArrayDimAssignedObjectRector::class,
        AddReturnDocblockForJsonArrayRector::class,
        // move to rules based on existing docblocks, as more risky
        // property var
        DocblockVarFromParamDocblockInConstructorRector::class,
        DocblockVarArrayFromGetterReturnRector::class,
        // return
        DocblockGetterReturnArrayFromPropertyDocblockVarRector::class,
    ];
}
