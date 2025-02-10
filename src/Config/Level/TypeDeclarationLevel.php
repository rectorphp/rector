<?php

declare (strict_types=1);
namespace Rector\Config\Level;

use Rector\Contract\Rector\RectorInterface;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\Class_\AddTestsVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Class_\ChildDoctrineRepositoryClassTypeRector;
use Rector\TypeDeclaration\Rector\Class_\MergeDateTimePropertyTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Class_\PropertyTypeFromStrictSetterGetterRector;
use Rector\TypeDeclaration\Rector\Class_\ReturnTypeFromStrictTernaryRector;
use Rector\TypeDeclaration\Rector\Class_\TypedPropertyFromCreateMockAssignRector;
use Rector\TypeDeclaration\Rector\Class_\TypedPropertyFromJMSSerializerAttributeTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeBasedOnPHPUnitDataProviderRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromPropertyTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromBooleanConstReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromBooleanStrictReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictScalarReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNullableTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromMockObjectRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnCastRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnDirectArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictFluentReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictParamRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromSymfonySerializerRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnUnionTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictStringParamConcatRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictScalarReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictStringReturnsRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureNeverReturnTypeRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Closure\ClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;
use Rector\TypeDeclaration\Rector\Function_\AddFunctionVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddParamTypeSplFixedArrayRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddReturnTypeDeclarationFromYieldsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector;
final class TypeDeclarationLevel
{
    /**
     * The rule order matters, as its used in withTypeCoverageLevel() method
     * Place the safest rules first, follow by more complex ones
     *
     * @var array<class-string<RectorInterface>>
     */
    public const RULES = [
        // php 7.1, start with closure first, as safest
        AddClosureVoidReturnTypeWhereNoReturnRector::class,
        AddFunctionVoidReturnTypeWhereNoReturnRector::class,
        AddTestsVoidReturnTypeWhereNoReturnRector::class,
        ReturnTypeFromMockObjectRector::class,
        TypedPropertyFromCreateMockAssignRector::class,
        AddArrowFunctionReturnTypeRector::class,
        BoolReturnTypeFromBooleanConstReturnsRector::class,
        ReturnTypeFromStrictNewArrayRector::class,
        // scalar and array from constant
        ReturnTypeFromStrictConstantReturnRector::class,
        StringReturnTypeFromStrictScalarReturnsRector::class,
        NumericReturnTypeFromStrictScalarReturnsRector::class,
        BoolReturnTypeFromBooleanStrictReturnsRector::class,
        StringReturnTypeFromStrictStringReturnsRector::class,
        NumericReturnTypeFromStrictReturnsRector::class,
        ReturnTypeFromStrictTernaryRector::class,
        ReturnTypeFromReturnDirectArrayRector::class,
        ResponseReturnTypeControllerActionRector::class,
        ReturnTypeFromReturnNewRector::class,
        ReturnTypeFromReturnCastRector::class,
        ReturnTypeFromSymfonySerializerRector::class,
        AddVoidReturnTypeWhereNoReturnRector::class,
        ReturnTypeFromStrictTypedPropertyRector::class,
        ReturnNullableTypeRector::class,
        // php 7.4
        EmptyOnNullableObjectToInstanceOfRector::class,
        // php 7.4
        TypedPropertyFromStrictConstructorRector::class,
        AddParamTypeSplFixedArrayRector::class,
        AddReturnTypeDeclarationFromYieldsRector::class,
        AddParamTypeBasedOnPHPUnitDataProviderRector::class,
        TypedPropertyFromStrictSetUpRector::class,
        ReturnTypeFromStrictNativeCallRector::class,
        ReturnTypeFromStrictTypedCallRector::class,
        ChildDoctrineRepositoryClassTypeRector::class,
        // param
        AddMethodCallBasedStrictParamTypeRector::class,
        ParamTypeByParentCallTypeRector::class,
        // multi types (nullable, union)
        ReturnUnionTypeRector::class,
        // closures
        AddClosureNeverReturnTypeRector::class,
        ClosureReturnTypeRector::class,
        // more risky rules
        ReturnTypeFromStrictParamRector::class,
        AddParamTypeFromPropertyTypeRector::class,
        MergeDateTimePropertyTypeDeclarationRector::class,
        PropertyTypeFromStrictSetterGetterRector::class,
        ParamTypeByMethodCallTypeRector::class,
        TypedPropertyFromAssignsRector::class,
        AddReturnTypeDeclarationBasedOnParentClassMethodRector::class,
        ReturnTypeFromStrictFluentReturnRector::class,
        ReturnNeverTypeRector::class,
        StrictArrayParamDimFetchRector::class,
        StrictStringParamConcatRector::class,
        TypedPropertyFromJMSSerializerAttributeTypeRector::class,
    ];
}
