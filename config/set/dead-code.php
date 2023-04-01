<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector;
use Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector;
use Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveLastReturnRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
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
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector;
use Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Rector\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector;
use Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;
use Rector\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector;
use Rector\DeadCode\Rector\StmtsAwareInterface\RemoveJustVariableAssignRector;
use Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
use Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;
use Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector;
use Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        UnwrapFutureCompatibleIfPhpVersionRector::class,
        RecastingRemovalRector::class,
        RemoveDeadStmtRector::class,
        RemoveDuplicatedArrayKeyRector::class,
        RemoveUnusedForeachKeyRector::class,
        RemoveParentCallWithoutParentRector::class,
        RemoveEmptyClassMethodRector::class,
        RemoveDoubleAssignRector::class,
        SimplifyMirrorAssignRector::class,
        RemoveUnusedPrivatePropertyRector::class,
        RemoveUnusedPrivateClassConstantRector::class,
        RemoveUnusedPrivateMethodRector::class,
        RemoveDeadReturnRector::class,
        RemoveDeadContinueRector::class,
        RemoveDeadIfForeachForRector::class,
        RemoveAndTrueRector::class,
        RemoveConcatAutocastRector::class,
        SimplifyUselessVariableRector::class,
        RemoveDelegatingParentCallRector::class,
        RemoveDuplicatedInstanceOfRector::class,
        RemoveDuplicatedCaseInSwitchRector::class,
        RemoveNullPropertyInitializationRector::class,
        RemoveUnreachableStatementRector::class,
        SimplifyIfElseWithSameContentRector::class,
        TernaryToBooleanOrFalseToBooleanAndRector::class,
        RemoveEmptyTestMethodRector::class,
        RemoveDeadTryCatchRector::class,
        RemoveUnusedVariableAssignRector::class,
        RemoveDuplicatedIfReturnRector::class,
        RemoveUnusedNonEmptyArrayBeforeForeachRector::class,
        RemoveEmptyMethodCallRector::class,
        RemoveDeadConditionAboveReturnRector::class,
        RemoveUnusedConstructorParamRector::class,
        RemoveDeadInstanceOfRector::class,
        RemoveDeadLoopRector::class,
        RemoveUnusedPrivateMethodParameterRector::class,
        // docblock
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
        RemoveNonExistingVarAnnotationRector::class,
        RemoveUselessVarTagRector::class,
        RemoveUnusedPromotedPropertyRector::class,
        RemoveLastReturnRector::class,
        RemoveJustPropertyFetchForAssignRector::class,
        RemoveJustVariableAssignRector::class,
        RemoveAlwaysTrueIfConditionRector::class,
        RemoveDeadZeroAndOneOperationRector::class,
    ]);
};
