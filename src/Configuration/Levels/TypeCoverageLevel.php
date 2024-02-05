<?php

declare (strict_types=1);
namespace Rector\Configuration\Levels;

use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\Class_\MergeDateTimePropertyTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Class_\PropertyTypeFromStrictSetterGetterRector;
use Rector\TypeDeclaration\Rector\Class_\ReturnTypeFromStrictTernaryRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeBasedOnPHPUnitDataProviderRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromPropertyTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromStrictScalarReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictScalarReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnDirectArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictParamRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnUnionTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictStringParamConcatRector;
use Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddParamTypeSplFixedArrayRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddReturnTypeDeclarationFromYieldsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector;
/**
 * Key 0 = level 0
 * Key 50 = level 50
 *
 * Start at 0, go slowly higher, one level per PR, and improve your type coverage
 *
 * From the safest rules to more changing ones.
 * @experimental Since 0.19.7 This list can change in time, based on community feedback,
 * what rules are safer than other. The safest rules will be always in the top.
 */
final class TypeCoverageLevel
{
    /**
     * Mind that return type declarations are the safest to add,
     * followed by property, then params
     *
     * @var array<class-string>
     */
    public const RULE_LIST = [
        // php 7.0
        AddVoidReturnTypeWhereNoReturnRector::class,
        // php 7.4
        AddArrowFunctionReturnTypeRector::class,
        ReturnTypeFromStrictNewArrayRector::class,
        ReturnTypeFromStrictConstantReturnRector::class,
        NumericReturnTypeFromStrictScalarReturnsRector::class,
        ReturnTypeFromStrictScalarReturnExprRector::class,
        ReturnTypeFromStrictBoolReturnExprRector::class,
        ReturnTypeFromStrictTernaryRector::class,
        EmptyOnNullableObjectToInstanceOfRector::class,
        // php 7.4
        TypedPropertyFromStrictConstructorRector::class,
        ReturnTypeFromReturnDirectArrayRector::class,
        AddParamTypeSplFixedArrayRector::class,
        AddReturnTypeDeclarationFromYieldsRector::class,
        AddParamTypeBasedOnPHPUnitDataProviderRector::class,
        // php 7.4
        TypedPropertyFromStrictSetUpRector::class,
        ReturnTypeFromReturnNewRector::class,
        BoolReturnTypeFromStrictScalarReturnsRector::class,
        ReturnTypeFromStrictNativeCallRector::class,
        ReturnTypeFromStrictTypedCallRector::class,
        // param
        AddMethodCallBasedStrictParamTypeRector::class,
        ParamTypeByParentCallTypeRector::class,
        ReturnUnionTypeRector::class,
        // more risky rules
        ReturnTypeFromStrictParamRector::class,
        AddParamTypeFromPropertyTypeRector::class,
        MergeDateTimePropertyTypeDeclarationRector::class,
        PropertyTypeFromStrictSetterGetterRector::class,
        StrictArrayParamDimFetchRector::class,
        StrictStringParamConcatRector::class,
    ];
}
