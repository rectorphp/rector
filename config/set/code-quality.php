<?php

declare(strict_types=1);

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\CodeQuality\Rector\Assign\CombinedAssignRector::class);
    $services->set(\Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector::class);
    $services->set(\Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector::class);
    $services->set(\Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector::class);
    $services->set(\Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector::class);
    $services->set(\Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector::class);
    $services->set(\Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector::class);
    $services->set(\Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector::class);
    $services->set(\Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector::class);
    $services->set(\Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector::class);
    $services->set(\Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector::class);
    $services->set(\Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector::class);
    $services->set(\Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector::class);
    $services->set(\Rector\CodeQuality\Rector\Concat\JoinStringConcatRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\CombineIfRector::class);
    $services->set(\Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector::class);
    $services->set(\Rector\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector::class);
    $services->set(\Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector::class);
    $services->set(\Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector::class);
    $services->set(\Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector::class);
    $services->set(\Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector::class);
    $services->set(\Rector\CodeQuality\Rector\For_\ForToForeachRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector::class);
    $services->set(\Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector::class);
    $services->set(\Rector\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector::class);
    $services->set(\Rector\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector::class);
    $services->set(\Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\ShortenElseIfRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector::class);
    $services->set(\Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector::class);
    $services->set(\Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector::class);
    $services->set(\Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector::class);
    $services->set(\Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector::class);
    $services->set(\Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector::class);
    $services->set(\Rector\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector::class);
    $services->set(\Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector::class);
    $services->set(\Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector::class);
    $services->set(\Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector::class);
    $services->set(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class)->call('configure', [[
        \Rector\Renaming\Rector\FuncCall\RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => [
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
    $services->set(\Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector::class);
    $services->set(\Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector::class);
    $services->set(\Rector\Php52\Rector\Property\VarToPublicPropertyRector::class);
    $services->set(\Rector\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector::class);
    $services->set(\Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector::class);
    $services->set(\Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector::class);
    $services->set(\Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector::class);
    $services->set(\Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector::class);
    $services->set(\Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector::class);
};
