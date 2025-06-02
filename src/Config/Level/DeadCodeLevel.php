<?php

declare (strict_types=1);
namespace Rector\Config\Level;

use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\Contract\Rector\RectorInterface;
use Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector;
use Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\DeadCode\Rector\Block\ReplaceBlockToItsStmtsRector;
use Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector;
use Rector\DeadCode\Rector\ClassLike\RemoveTypedPropertyNonMockDocblockRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveArgumentFromDefaultParentCallRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveNullTagValueNodeRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessAssignFromPropertyPromotionRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnExprInConstructRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Rector\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector;
use Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector;
use Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector;
use Rector\DeadCode\Rector\For_\RemoveDeadContinueRector;
use Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector;
use Rector\DeadCode\Rector\For_\RemoveDeadLoopRector;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\DeadCode\Rector\FuncCall\RemoveFilterVarOnExactTypeRector;
use Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Rector\DeadCode\Rector\If_\ReduceAlwaysFalseIfOrRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\DeadCode\Rector\If_\RemoveTypedPropertyDeadInstanceOfRector;
use Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector;
use Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\DeadCode\Rector\Property\RemoveUselessReadOnlyTagRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;
use Rector\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
use Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;
use Rector\DeadCode\Rector\TryCatch\RemoveDeadCatchRector;
use Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector;
/**
 * Key 0 = level 0
 * Key 50 = level 50
 *
 * Start at 0, go slowly higher, one level per PR, and improve your rule coverage
 *
 * From the safest rules to more changing ones.
 *
 * @experimental Since 0.19.7 This list can change in time, based on community feedback,
 * what rules are safer than other. The safest rules will be always in the top.
 */
final class DeadCodeLevel
{
    /**
     * Mind that return type declarations are the safest to add,
     * followed by property, then params
     *
     * @var array<class-string<RectorInterface>>
     */
    public const RULES = [
        // easy picks
        RemoveUnusedForeachKeyRector::class,
        RemoveDuplicatedArrayKeyRector::class,
        RecastingRemovalRector::class,
        RemoveAndTrueRector::class,
        SimplifyMirrorAssignRector::class,
        RemoveDeadContinueRector::class,
        RemoveUnusedNonEmptyArrayBeforeForeachRector::class,
        RemoveNullPropertyInitializationRector::class,
        RemoveUselessReturnExprInConstructRector::class,
        ReplaceBlockToItsStmtsRector::class,
        RemoveFilterVarOnExactTypeRector::class,
        RemoveTypedPropertyDeadInstanceOfRector::class,
        TernaryToBooleanOrFalseToBooleanAndRector::class,
        RemoveDoubleAssignRector::class,
        RemoveUselessAssignFromPropertyPromotionRector::class,
        RemoveConcatAutocastRector::class,
        SimplifyIfElseWithSameContentRector::class,
        SimplifyUselessVariableRector::class,
        RemoveDeadZeroAndOneOperationRector::class,
        // docblock
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
        RemoveUselessReadOnlyTagRector::class,
        RemoveNonExistingVarAnnotationRector::class,
        RemoveUselessVarTagRector::class,
        // prioritize safe belt on RemoveUseless*TagRector that registered previously first
        RemoveNullTagValueNodeRector::class,
        RemovePhpVersionIdCheckRector::class,
        RemoveTypedPropertyNonMockDocblockRector::class,
        RemoveAlwaysTrueIfConditionRector::class,
        ReduceAlwaysFalseIfOrRector::class,
        RemoveUnusedPrivateClassConstantRector::class,
        RemoveUnusedPrivatePropertyRector::class,
        RemoveDuplicatedCaseInSwitchRector::class,
        RemoveDeadInstanceOfRector::class,
        RemoveDeadCatchRector::class,
        RemoveDeadTryCatchRector::class,
        RemoveDeadIfForeachForRector::class,
        RemoveDeadStmtRector::class,
        UnwrapFutureCompatibleIfPhpVersionRector::class,
        RemoveParentCallWithoutParentRector::class,
        RemoveDeadConditionAboveReturnRector::class,
        RemoveDeadLoopRector::class,
        // removing methods could be risky if there is some magic loading them
        RemoveUnusedPromotedPropertyRector::class,
        RemoveUnusedPrivateMethodParameterRector::class,
        RemoveUnusedPublicMethodParameterRector::class,
        RemoveUnusedPrivateMethodRector::class,
        RemoveUnreachableStatementRector::class,
        RemoveUnusedVariableAssignRector::class,
        // this could break framework magic autowiring in some cases
        RemoveUnusedConstructorParamRector::class,
        RemoveEmptyClassMethodRector::class,
        RemoveDeadReturnRector::class,
        RemoveArgumentFromDefaultParentCallRector::class,
    ];
}
