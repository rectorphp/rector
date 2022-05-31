<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\CodingStyle\Rector\Assign\PHPStormVarAnnotationRector;
use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use Rector\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector;
use Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector;
use Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector;
use Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector;
use Rector\CodingStyle\Rector\Ternary\TernaryConditionVariableAssignmentRector;
use Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Transform\Rector\FuncCall\FuncCallToConstFetchRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\FuncCall\FuncCallToConstFetchRector::class, ['php_sapi_name' => 'PHP_SAPI', 'pi' => 'M_PI']);
    $rectorConfig->rules([\Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector::class, \Rector\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector::class, \Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector::class, \Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector::class, \Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector::class, \Rector\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector::class, \Rector\CodingStyle\Rector\Assign\PHPStormVarAnnotationRector::class, \Rector\CodingStyle\Rector\If_\NullableCompareToNullRector::class, \Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector::class, \Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector::class, \Rector\CodingStyle\Rector\Ternary\TernaryConditionVariableAssignmentRector::class, \Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector::class, \Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector::class, \Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class, \Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector::class, \Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector::class, \Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector::class, \Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector::class, \Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector::class, \Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector::class, \Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector::class, \Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector::class, \Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector::class, \Rector\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector::class, \Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector::class, \Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector::class, \Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector::class]);
};
