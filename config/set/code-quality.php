<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodeQuality\Rector\BooleanAnd\RemoveUselessIsObjectCheckRector;
use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\BooleanNot\ReplaceMultipleBooleanNotRector;
use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\ClassConstFetch\ConvertStaticPrivateConstantToSelfRector;
use Rector\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector;
use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\Expression\TernaryFalseExpressionToIfRector;
use Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector;
use Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector;
use Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector;
use Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector;
use Rector\CodeQuality\Rector\FuncCall\BoolvalToTypeCastRector;
use Rector\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\CodeQuality\Rector\FuncCall\FloatvalToTypeCastRector;
use Rector\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector;
use Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector;
use Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector;
use Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector;
use Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use Rector\CodeQuality\Rector\FuncCall\StrvalToTypeCastRector;
use Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector;
use Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector;
use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector;
use Rector\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\CompleteMissingIfElseBracketRector;
use Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector;
use Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector;
use Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector;
use Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector;
use Rector\CodeQuality\Rector\NullsafeMethodCall\CleanupUnneededNullsafeOperatorRector;
use Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector;
use Rector\CodeQuality\Rector\Switch_\SwitchTrueToIfRector;
use Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Rector\CodeQuality\Rector\Ternary\NumberCompareToMaxFuncCallRector;
use Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector;
use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Rector\CodeQuality\Rector\Ternary\TernaryEmptyArrayArrayDimFetchToCoalesceRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Config\RectorConfig;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        'split' => 'explode',
        'join' => 'implode',
        'sizeof' => 'count',
        # https://www.php.net/manual/en/aliases.php
        'chop' => 'rtrim',
        'doubleval' => 'floatval',
        'gzputs' => 'gzwrites',
        'fputs' => 'fwrite',
        'ini_alter' => 'ini_set',
        'is_double' => 'is_float',
        'is_integer' => 'is_int',
        'is_long' => 'is_int',
        'is_real' => 'is_float',
        'is_writeable' => 'is_writable',
        'key_exists' => 'array_key_exists',
        'pos' => 'current',
        'strchr' => 'strstr',
        # mb
        'mbstrcut' => 'mb_strcut',
        'mbstrlen' => 'mb_strlen',
        'mbstrpos' => 'mb_strpos',
        'mbstrrpos' => 'mb_strrpos',
        'mbsubstr' => 'mb_substr',
    ]);
    $rectorConfig->rules([CombinedAssignRector::class, SimplifyEmptyArrayCheckRector::class, ReplaceMultipleBooleanNotRector::class, ForeachToInArrayRector::class, SimplifyForeachToCoalescingRector::class, SimplifyFuncGetArgsCountRector::class, SimplifyInArrayValuesRector::class, SimplifyStrposLowerRector::class, GetClassToInstanceOfRector::class, SimplifyArraySearchRector::class, SimplifyConditionsRector::class, SimplifyIfNotNullReturnRector::class, SimplifyIfReturnBoolRector::class, SimplifyUselessVariableRector::class, UnnecessaryTernaryExpressionRector::class, RemoveExtraParametersRector::class, SimplifyDeMorganBinaryRector::class, SimplifyTautologyTernaryRector::class, SingleInArrayToCompareRector::class, SimplifyIfElseToTernaryRector::class, JoinStringConcatRector::class, ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class, ExplicitBoolCompareRector::class, CombineIfRector::class, UseIdenticalOverEqualWithSameTypeRector::class, SimplifyBoolIdenticalTrueRector::class, SimplifyRegexPatternRector::class, BooleanNotIdenticalToNotIdenticalRector::class, StrvalToTypeCastRector::class, FloatvalToTypeCastRector::class, CallableThisArrayToAnonymousFunctionRector::class, AndAssignsToSeparateLinesRector::class, CompactToVariablesRector::class, CompleteDynamicPropertiesRector::class, IsAWithStringWithThirdArgumentRector::class, StrlenZeroToIdenticalEmptyStringRector::class, ThrowWithPreviousExceptionRector::class, RemoveSoleValueSprintfRector::class, ShortenElseIfRector::class, ArrayMergeOfNonArraysToSimpleArrayRector::class, IntvalToTypeCastRector::class, BoolvalToTypeCastRector::class, ArrayKeyExistsTernaryThenValueToCoalescingRector::class, AbsolutizeRequireAndIncludePathRector::class, ChangeArrayPushToArrayAssignRector::class, ForRepeatedCountToOwnVariableRector::class, ForeachItemsAssignToEmptyArrayToAssignRector::class, InlineIfToExplicitIfRector::class, UnusedForeachValueToArrayKeysRector::class, CommonNotEqualRector::class, SetTypeToCastRector::class, LogicalToBooleanRector::class, VarToPublicPropertyRector::class, IssetOnPropertyObjectToPropertyExistsRector::class, NewStaticToNewSelfRector::class, UnwrapSprintfOneArgumentRector::class, SwitchNegatedTernaryRector::class, SingularSwitchToIfRector::class, SimplifyIfNullableReturnRector::class, FuncGetArgsToVariadicParamRector::class, CallUserFuncToMethodCallRector::class, CallUserFuncWithArrowFunctionToInlineRector::class, CountArrayToEmptyArrayComparisonRector::class, FlipTypeControlToUseExclusiveTypeRector::class, InlineArrayReturnAssignRector::class, InlineIsAInstanceOfRector::class, TernaryFalseExpressionToIfRector::class, InlineConstructorDefaultToPropertyRector::class, TernaryEmptyArrayArrayDimFetchToCoalesceRector::class, OptionalParametersAfterRequiredRector::class, SimplifyEmptyCheckOnEmptyArrayRector::class, SwitchTrueToIfRector::class, CleanupUnneededNullsafeOperatorRector::class, DisallowedEmptyRuleFixerRector::class, ConvertStaticPrivateConstantToSelfRector::class, LocallyCalledStaticMethodToNonStaticRector::class, NumberCompareToMaxFuncCallRector::class, CompleteMissingIfElseBracketRector::class, RemoveUselessIsObjectCheckRector::class]);
};
