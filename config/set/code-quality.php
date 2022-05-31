<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector;
use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector;
use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\BooleanNot\ReplaceMultipleBooleanNotRector;
use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector;
use Rector\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector;
use Rector\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector;
use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\CodeQuality\Rector\Do_\DoWhileBreakFalseToIfElseRector;
use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector;
use Rector\CodeQuality\Rector\For_\ForToForeachRector;
use Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector;
use Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector;
use Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector;
use Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector;
use Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector;
use Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector;
use Rector\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
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
use Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector;
use Rector\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector;
use Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector;
use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector;
use Rector\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector;
use Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector;
use Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector;
use Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector;
use Rector\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector;
use Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector;
use Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector;
use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Config\RectorConfig;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class, [
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
    $rectorConfig->rules([\Rector\CodeQuality\Rector\Assign\CombinedAssignRector::class, \Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector::class, \Rector\CodeQuality\Rector\BooleanNot\ReplaceMultipleBooleanNotRector::class, \Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector::class, \Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector::class, \Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector::class, \Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector::class, \Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector::class, \Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector::class, \Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector::class, \Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector::class, \Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector::class, \Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector::class, \Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector::class, \Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector::class, \Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector::class, \Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector::class, \Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector::class, \Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector::class, \Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector::class, \Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector::class, \Rector\CodeQuality\Rector\Concat\JoinStringConcatRector::class, \Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class, \Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector::class, \Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector::class, \Rector\CodeQuality\Rector\If_\CombineIfRector::class, \Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector::class, \Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector::class, \Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector::class, \Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector::class, \Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector::class, \Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector::class, \Rector\CodeQuality\Rector\For_\ForToForeachRector::class, \Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector::class, \Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector::class, \Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector::class, \Rector\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector::class, \Rector\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector::class, \Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector::class, \Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector::class, \Rector\CodeQuality\Rector\If_\ShortenElseIfRector::class, \Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector::class, \Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector::class, \Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector::class, \Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector::class, \Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector::class, \Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector::class, \Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector::class, \Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector::class, \Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector::class, \Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector::class, \Rector\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector::class, \Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector::class, \Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector::class, \Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector::class, \Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector::class, \Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector::class, \Rector\Php52\Rector\Property\VarToPublicPropertyRector::class, \Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector::class, \Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector::class, \Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector::class, \Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector::class, \Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector::class, \Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector::class, \Rector\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector::class, \Rector\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector::class, \Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector::class, \Rector\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector::class, \Rector\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector::class, \Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector::class, \Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector::class, \Rector\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector::class, \Rector\CodeQuality\Rector\Do_\DoWhileBreakFalseToIfElseRector::class, \Rector\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector::class, \Rector\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector::class]);
};
