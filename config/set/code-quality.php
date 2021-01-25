<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector;
use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector;
use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector;
use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
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
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\CodeQuality\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector;
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
use Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector;
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
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector;
use Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector;
use Rector\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector;
use Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector;
use Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector;
use Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector;
use Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Rector\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector;
use Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector;
use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(CombinedAssignRector::class);
    $services->set(SimplifyEmptyArrayCheckRector::class);
    $services->set(ForeachToInArrayRector::class);
    $services->set(SimplifyForeachToCoalescingRector::class);
    $services->set(InArrayAndArrayKeysToArrayKeyExistsRector::class);
    $services->set(SimplifyFuncGetArgsCountRector::class);
    $services->set(SimplifyInArrayValuesRector::class);
    $services->set(SimplifyStrposLowerRector::class);
    $services->set(GetClassToInstanceOfRector::class);
    $services->set(SimplifyArraySearchRector::class);
    $services->set(SimplifyConditionsRector::class);
    $services->set(SimplifyIfNotNullReturnRector::class);
    $services->set(SimplifyIfReturnBoolRector::class);
    $services->set(SimplifyUselessVariableRector::class);
    $services->set(UnnecessaryTernaryExpressionRector::class);
    $services->set(RemoveExtraParametersRector::class);
    $services->set(SimplifyDeMorganBinaryRector::class);
    $services->set(SimplifyTautologyTernaryRector::class);
    $services->set(SimplifyForeachToArrayFilterRector::class);
    $services->set(SingleInArrayToCompareRector::class);
    $services->set(SimplifyIfElseToTernaryRector::class);
    $services->set(JoinStringConcatRector::class);
    $services->set(ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class);
    $services->set(SimplifyIfIssetToNullCoalescingRector::class);
    $services->set(ExplicitBoolCompareRector::class);
    $services->set(CombineIfRector::class);
    $services->set(UseIdenticalOverEqualWithSameTypeRector::class);
    $services->set(SimplifyDuplicatedTernaryRector::class);
    $services->set(SimplifyBoolIdenticalTrueRector::class);
    $services->set(SimplifyRegexPatternRector::class);
    $services->set(BooleanNotIdenticalToNotIdenticalRector::class);
    $services->set(CallableThisArrayToAnonymousFunctionRector::class);
    $services->set(AndAssignsToSeparateLinesRector::class);
    $services->set(ForToForeachRector::class);
    $services->set(CompactToVariablesRector::class);
    $services->set(CompleteDynamicPropertiesRector::class);
    $services->set(IsAWithStringWithThirdArgumentRector::class);
    $services->set(StrlenZeroToIdenticalEmptyStringRector::class);
    $services->set(RemoveAlwaysTrueConditionSetInConstructorRector::class);
    $services->set(ThrowWithPreviousExceptionRector::class);
    $services->set(RemoveSoleValueSprintfRector::class);
    $services->set(ShortenElseIfRector::class);
    $services->set(AddPregQuoteDelimiterRector::class);
    $services->set(ArrayMergeOfNonArraysToSimpleArrayRector::class);
    $services->set(IntvalToTypeCastRector::class);
    $services->set(ArrayKeyExistsTernaryThenValueToCoalescingRector::class);
    $services->set(AbsolutizeRequireAndIncludePathRector::class);
    $services->set(ChangeArrayPushToArrayAssignRector::class);
    $services->set(ForRepeatedCountToOwnVariableRector::class);
    $services->set(ForeachItemsAssignToEmptyArrayToAssignRector::class);
    $services->set(InlineIfToExplicitIfRector::class);
    $services->set(ArrayKeysAndInArrayToArrayKeyExistsRector::class);
    $services->set(SplitListAssignToSeparateLineRector::class);
    $services->set(UnusedForeachValueToArrayKeysRector::class);
    $services->set(ArrayThisCallToThisMethodCallRector::class);
    $services->set(CommonNotEqualRector::class);
    $services->set(RenameFunctionRector::class)->call('configure', [[
        RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => [
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
        ],
    ]]);
    $services->set(SetTypeToCastRector::class);
    $services->set(LogicalToBooleanRector::class);
    $services->set(VarToPublicPropertyRector::class);
    $services->set(FixClassCaseSensitivityNameRector::class);
    $services->set(IssetOnPropertyObjectToPropertyExistsRector::class);
    $services->set(NewStaticToNewSelfRector::class);
    $services->set(DateTimeToDateTimeInterfaceRector::class);
    $services->set(UnwrapSprintfOneArgumentRector::class);
    $services->set(SwitchNegatedTernaryRector::class);
    $services->set(SingularSwitchToIfRector::class);
};
