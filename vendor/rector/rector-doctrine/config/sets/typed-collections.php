<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Doctrine\TypedCollections\Rector\Assign\ArrayDimFetchAssignToAddCollectionCallRector;
use Rector\Doctrine\TypedCollections\Rector\Assign\ArrayOffsetSetToSetCollectionCallRector;
use Rector\Doctrine\TypedCollections\Rector\Class_\CompleteParamDocblockFromSetterToCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\Class_\CompletePropertyDocblockFromToManyRector;
use Rector\Doctrine\TypedCollections\Rector\Class_\CompleteReturnDocblockFromToManyRector;
use Rector\Doctrine\TypedCollections\Rector\Class_\InitializeCollectionInConstructorRector;
use Rector\Doctrine\TypedCollections\Rector\Class_\RemoveNullFromInstantiatedArrayCollectionPropertyRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\CollectionGetterNativeTypeRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\CollectionParamTypeSetterToCollectionPropertyRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\CollectionSetterParamNativeTypeRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\DefaultCollectionKeyRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\NarrowArrayCollectionToCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\NarrowParamUnionToCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\NarrowReturnUnionToCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\RemoveNewArrayCollectionOutsideConstructorRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\RemoveNullFromNullableCollectionTypeRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\ReturnArrayToNewArrayCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\ReturnCollectionDocblockRector;
use Rector\Doctrine\TypedCollections\Rector\Empty_\EmptyOnCollectionToIsEmptyCallRector;
use Rector\Doctrine\TypedCollections\Rector\Expression\RemoveAssertNotNullOnCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\Expression\RemoveCoalesceAssignOnCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\FuncCall\ArrayMapOnCollectionToArrayRector;
use Rector\Doctrine\TypedCollections\Rector\FuncCall\ArrayMergeOnCollectionToArrayRector;
use Rector\Doctrine\TypedCollections\Rector\FuncCall\CurrentOnCollectionToArrayRector;
use Rector\Doctrine\TypedCollections\Rector\FuncCall\InArrayOnCollectionToContainsCallRector;
use Rector\Doctrine\TypedCollections\Rector\If_\RemoveIfCollectionIdenticalToNullRector;
use Rector\Doctrine\TypedCollections\Rector\If_\RemoveIfInstanceofCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\If_\RemoveIsArrayOnCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\If_\RemoveUselessIsEmptyAssignRector;
use Rector\Doctrine\TypedCollections\Rector\MethodCall\AssertNullOnCollectionToAssertEmptyRector;
use Rector\Doctrine\TypedCollections\Rector\MethodCall\AssertSameCountOnCollectionToAssertCountRector;
use Rector\Doctrine\TypedCollections\Rector\MethodCall\SetArrayToNewCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\New_\RemoveNewArrayCollectionWrapRector;
use Rector\Doctrine\TypedCollections\Rector\NullsafeMethodCall\RemoveNullsafeOnCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\Property\NarrowPropertyUnionToCollectionRector;
use Rector\Doctrine\TypedCollections\Rector\Property\TypedPropertyFromToManyRelationTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // init
        InitializeCollectionInConstructorRector::class,
        RemoveNullFromInstantiatedArrayCollectionPropertyRector::class,
        RemoveNewArrayCollectionOutsideConstructorRector::class,
        // cleanups
        RemoveCoalesceAssignOnCollectionRector::class,
        RemoveIfInstanceofCollectionRector::class,
        RemoveIsArrayOnCollectionRector::class,
        RemoveIfCollectionIdenticalToNullRector::class,
        // collection method calls
        ArrayDimFetchAssignToAddCollectionCallRector::class,
        ArrayOffsetSetToSetCollectionCallRector::class,
        ArrayMapOnCollectionToArrayRector::class,
        ArrayMergeOnCollectionToArrayRector::class,
        CurrentOnCollectionToArrayRector::class,
        EmptyOnCollectionToIsEmptyCallRector::class,
        InArrayOnCollectionToContainsCallRector::class,
        // native type declarations
        CollectionGetterNativeTypeRector::class,
        CollectionSetterParamNativeTypeRector::class,
        CollectionParamTypeSetterToCollectionPropertyRector::class,
        TypedPropertyFromToManyRelationTypeRector::class,
        RemoveNullFromNullableCollectionTypeRector::class,
        // docblocks
        DefaultCollectionKeyRector::class,
        NarrowArrayCollectionToCollectionRector::class,
        // @param docblock
        CompleteParamDocblockFromSetterToCollectionRector::class,
        NarrowParamUnionToCollectionRector::class,
        // @var docblock
        CompletePropertyDocblockFromToManyRector::class,
        NarrowPropertyUnionToCollectionRector::class,
        // @return docblock
        NarrowReturnUnionToCollectionRector::class,
        CompleteReturnDocblockFromToManyRector::class,
        ReturnCollectionDocblockRector::class,
        // new ArrayCollection() wraps
        ReturnArrayToNewArrayCollectionRector::class,
        SetArrayToNewCollectionRector::class,
        RemoveNewArrayCollectionWrapRector::class,
        // cleanup
        RemoveNullsafeOnCollectionRector::class,
        RemoveUselessIsEmptyAssignRector::class,
        // test assertions
        RemoveAssertNotNullOnCollectionRector::class,
        AssertNullOnCollectionToAssertEmptyRector::class,
        AssertSameCountOnCollectionToAssertCountRector::class,
    ]);
};
