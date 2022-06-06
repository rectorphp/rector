<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod\RemoveLastReturnRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParamInRequiredAutowireRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\For_\RemoveDeadContinueRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\For_\RemoveDeadLoopRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;
use RectorPrefix20220606\Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        UnwrapFutureCompatibleIfFunctionExistsRector::class,
        UnwrapFutureCompatibleIfPhpVersionRector::class,
        RecastingRemovalRector::class,
        RemoveDeadStmtRector::class,
        RemoveDuplicatedArrayKeyRector::class,
        RemoveUnusedForeachKeyRector::class,
        RemoveParentCallWithoutParentRector::class,
        RemoveEmptyClassMethodRector::class,
        RemoveDoubleAssignRector::class,
        SimplifyMirrorAssignRector::class,
        RemoveOverriddenValuesRector::class,
        RemoveUnusedPrivatePropertyRector::class,
        RemoveUnusedPrivateClassConstantRector::class,
        RemoveUnusedPrivateMethodRector::class,
        RemoveDeadConstructorRector::class,
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
        RemoveUnusedParamInRequiredAutowireRector::class,
        // docblock
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
        RemoveNonExistingVarAnnotationRector::class,
        RemoveUnusedPromotedPropertyRector::class,
        RemoveLastReturnRector::class,
        RemoveJustPropertyFetchForAssignRector::class,
    ]);
};
