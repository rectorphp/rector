<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

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
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Assign\CombinedAssignRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\BooleanNot\ReplaceMultipleBooleanNotRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector::class);
    $rectorConfig->rule(\Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Concat\JoinStringConcatRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\If_\CombineIfRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\For_\ForToForeachRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\If_\ShortenElseIfRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector::class);
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
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector::class);
    $rectorConfig->rule(\Rector\Php52\Rector\Property\VarToPublicPropertyRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector::class);
    $rectorConfig->rule(\Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector::class);
    $rectorConfig->rule(\Rector\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector::class);
    $rectorConfig->rule(\Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\Do_\DoWhileBreakFalseToIfElseRector::class);
    $rectorConfig->rule(\Rector\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector::class);
};
