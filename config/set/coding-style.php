<?php

declare (strict_types=1);
namespace RectorPrefix202409;

use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector;
use Rector\CodingStyle\Rector\ClassConst\SplitGroupedClassConstantsRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\CodingStyle\Rector\Property\SplitGroupedPropertiesRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\Stmt\RemoveUselessAliasInUseStatementRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector;
use Rector\CodingStyle\Rector\Ternary\TernaryConditionVariableAssignmentRector;
use Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Transform\Rector\FuncCall\FuncCallToConstFetchRector;
use Rector\Visibility\Rector\ClassMethod\ExplicitPublicClassMethodRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(FuncCallToConstFetchRector::class, ['php_sapi_name' => 'PHP_SAPI', 'pi' => 'M_PI']);
    $rectorConfig->rules([SeparateMultiUseImportsRector::class, NewlineAfterStatementRector::class, RemoveFinalFromConstRector::class, NullableCompareToNullRector::class, ConsistentImplodeRector::class, TernaryConditionVariableAssignmentRector::class, SymplifyQuoteEscapeRector::class, StringClassNameToClassConstantRector::class, CatchExceptionNameMatchingTypeRector::class, SplitDoubleAssignRector::class, EncapsedStringsToSprintfRector::class, WrapEncapsedVariableInCurlyBracesRector::class, NewlineBeforeNewAssignSetRector::class, MakeInheritedMethodVisibilitySameAsParentRector::class, CallUserFuncArrayToVariadicRector::class, VersionCompareFuncCallToConstantRector::class, CountArrayToEmptyArrayComparisonRector::class, CallUserFuncToMethodCallRector::class, FuncGetArgsToVariadicParamRector::class, StrictArraySearchRector::class, UseClassKeywordForClassNameResolutionRector::class, SplitGroupedPropertiesRector::class, SplitGroupedClassConstantsRector::class, ExplicitPublicClassMethodRector::class, RemoveUselessAliasInUseStatementRector::class]);
};
