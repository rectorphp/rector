<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector;
use Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector;
use Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveLastReturnRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParamInRequiredAutowireRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector;
use Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector;
use Rector\DeadCode\Rector\For_\RemoveDeadContinueRector;
use Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector;
use Rector\DeadCode\Rector\For_\RemoveDeadLoopRector;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Rector\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector;
use Rector\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector;
use Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Rector\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector;
use Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;
use Rector\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
use Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;
use Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector;
use Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        \Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector::class,
        \Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector::class,
        \Rector\DeadCode\Rector\Cast\RecastingRemovalRector::class,
        \Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector::class,
        \Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector::class,
        \Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector::class,
        \Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector::class,
        \Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector::class,
        \Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector::class,
        \Rector\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector::class,
        \Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector::class,
        \Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector::class,
        \Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector::class,
        \Rector\DeadCode\Rector\For_\RemoveDeadContinueRector::class,
        \Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector::class,
        \Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector::class,
        \Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector::class,
        \Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector::class,
        \Rector\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector::class,
        \Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector::class,
        \Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector::class,
        \Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector::class,
        \Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector::class,
        \Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector::class,
        \Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector::class,
        \Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector::class,
        \Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector::class,
        \Rector\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector::class,
        \Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector::class,
        \Rector\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector::class,
        \Rector\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector::class,
        \Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector::class,
        \Rector\DeadCode\Rector\For_\RemoveDeadLoopRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParamInRequiredAutowireRector::class,
        // docblock
        \Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector::class,
        \Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveLastReturnRector::class,
    ]);
};
