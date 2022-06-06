<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\BooleanNot\ReplaceMultipleBooleanNotRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Do_\DoWhileBreakFalseToIfElseRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\For_\ForToForeachRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\If_\CombineIfRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use RectorPrefix20220606\Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use RectorPrefix20220606\Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use RectorPrefix20220606\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
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
    $rectorConfig->rules([CombinedAssignRector::class, SimplifyEmptyArrayCheckRector::class, ReplaceMultipleBooleanNotRector::class, ForeachToInArrayRector::class, SimplifyForeachToCoalescingRector::class, SimplifyFuncGetArgsCountRector::class, SimplifyInArrayValuesRector::class, SimplifyStrposLowerRector::class, GetClassToInstanceOfRector::class, SimplifyArraySearchRector::class, SimplifyConditionsRector::class, SimplifyIfNotNullReturnRector::class, SimplifyIfReturnBoolRector::class, SimplifyUselessVariableRector::class, UnnecessaryTernaryExpressionRector::class, RemoveExtraParametersRector::class, SimplifyDeMorganBinaryRector::class, SimplifyTautologyTernaryRector::class, SimplifyForeachToArrayFilterRector::class, SingleInArrayToCompareRector::class, SimplifyIfElseToTernaryRector::class, JoinStringConcatRector::class, ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class, SimplifyIfIssetToNullCoalescingRector::class, ExplicitBoolCompareRector::class, CombineIfRector::class, UseIdenticalOverEqualWithSameTypeRector::class, SimplifyBoolIdenticalTrueRector::class, SimplifyRegexPatternRector::class, BooleanNotIdenticalToNotIdenticalRector::class, CallableThisArrayToAnonymousFunctionRector::class, AndAssignsToSeparateLinesRector::class, ForToForeachRector::class, CompactToVariablesRector::class, CompleteDynamicPropertiesRector::class, IsAWithStringWithThirdArgumentRector::class, StrlenZeroToIdenticalEmptyStringRector::class, RemoveAlwaysTrueConditionSetInConstructorRector::class, ThrowWithPreviousExceptionRector::class, RemoveSoleValueSprintfRector::class, ShortenElseIfRector::class, AddPregQuoteDelimiterRector::class, ArrayMergeOfNonArraysToSimpleArrayRector::class, IntvalToTypeCastRector::class, ArrayKeyExistsTernaryThenValueToCoalescingRector::class, AbsolutizeRequireAndIncludePathRector::class, ChangeArrayPushToArrayAssignRector::class, ForRepeatedCountToOwnVariableRector::class, ForeachItemsAssignToEmptyArrayToAssignRector::class, InlineIfToExplicitIfRector::class, ArrayKeysAndInArrayToArrayKeyExistsRector::class, SplitListAssignToSeparateLineRector::class, UnusedForeachValueToArrayKeysRector::class, ArrayThisCallToThisMethodCallRector::class, CommonNotEqualRector::class, SetTypeToCastRector::class, LogicalToBooleanRector::class, VarToPublicPropertyRector::class, IssetOnPropertyObjectToPropertyExistsRector::class, NewStaticToNewSelfRector::class, DateTimeToDateTimeInterfaceRector::class, UnwrapSprintfOneArgumentRector::class, SwitchNegatedTernaryRector::class, SingularSwitchToIfRector::class, SimplifyIfNullableReturnRector::class, NarrowUnionTypeDocRector::class, FuncGetArgsToVariadicParamRector::class, CallUserFuncToMethodCallRector::class, CallUserFuncWithArrowFunctionToInlineRector::class, CountArrayToEmptyArrayComparisonRector::class, FlipTypeControlToUseExclusiveTypeRector::class, ExplicitMethodCallOverMagicGetSetRector::class, DoWhileBreakFalseToIfElseRector::class, InlineArrayReturnAssignRector::class, InlineIsAInstanceOfRector::class]);
};
