<?php

declare (strict_types=1);
namespace Rector\Config\Level;

use Rector\Contract\Rector\RectorInterface;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamArrayDocblockBasedOnCallableNativeFuncCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnDocblockForScalarArrayFromAssignsRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\AddVarArrayDocblockFromDimFetchAssignRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\ClassMethodArrayDocblockParamFromLocalCallsRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromGetterReturnRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromPropertyDefaultsRector;
use Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarFromParamDocblockInConstructorRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromAssignsParamToParamReferenceRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDimFetchAccessRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForArrayDimAssignedObjectRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForCommonObjectDenominatorRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForJsonArrayRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockGetterReturnArrayFromPropertyDocblockVarRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockReturnArrayFromDirectArrayInstanceRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\NarrowArrayCollectionUnionReturnDocblockRector;
final class TypeDeclarationDocblocksLevel
{
    /**
     * @var array<class-string<RectorInterface>>
     */
    public const RULES = [
        // start with rules based on native code
        // property var
        DocblockVarArrayFromPropertyDefaultsRector::class,
        // param
        AddParamArrayDocblockFromDimFetchAccessRector::class,
        ClassMethodArrayDocblockParamFromLocalCallsRector::class,
        AddParamArrayDocblockFromAssignsParamToParamReferenceRector::class,
        AddParamArrayDocblockBasedOnCallableNativeFuncCallRector::class,
        // return
        AddReturnDocblockForCommonObjectDenominatorRector::class,
        AddReturnDocblockForScalarArrayFromAssignsRector::class,
        DocblockReturnArrayFromDirectArrayInstanceRector::class,
        AddReturnDocblockForArrayDimAssignedObjectRector::class,
        AddReturnDocblockForJsonArrayRector::class,
        // move to rules based on existing docblocks, as more risky
        // property var
        DocblockVarFromParamDocblockInConstructorRector::class,
        DocblockVarArrayFromGetterReturnRector::class,
        AddVarArrayDocblockFromDimFetchAssignRector::class,
        // return
        DocblockGetterReturnArrayFromPropertyDocblockVarRector::class,
        NarrowArrayCollectionUnionReturnDocblockRector::class,
    ];
}
