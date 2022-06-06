<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\CodingStyle\Rector\Assign\PHPStormVarAnnotationRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\Ternary\TernaryConditionVariableAssignmentRector;
use RectorPrefix20220606\Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use RectorPrefix20220606\Rector\Transform\Rector\FuncCall\FuncCallToConstFetchRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(FuncCallToConstFetchRector::class, ['php_sapi_name' => 'PHP_SAPI', 'pi' => 'M_PI']);
    $rectorConfig->rules([SeparateMultiUseImportsRector::class, RemoveDoubleUnderscoreInMethodNameRector::class, PostIncDecToPreIncDecRector::class, UnSpreadOperatorRector::class, NewlineAfterStatementRector::class, RemoveFinalFromConstRector::class, PHPStormVarAnnotationRector::class, NullableCompareToNullRector::class, BinarySwitchToIfElseRector::class, ConsistentImplodeRector::class, TernaryConditionVariableAssignmentRector::class, SymplifyQuoteEscapeRector::class, SplitGroupedConstantsAndPropertiesRector::class, StringClassNameToClassConstantRector::class, ConsistentPregDelimiterRector::class, CatchExceptionNameMatchingTypeRector::class, UseIncrementAssignRector::class, SplitDoubleAssignRector::class, VarConstantCommentRector::class, EncapsedStringsToSprintfRector::class, WrapEncapsedVariableInCurlyBracesRector::class, NewlineBeforeNewAssignSetRector::class, AddArrayDefaultToArrayPropertyRector::class, AddFalseDefaultToBoolPropertyRector::class, MakeInheritedMethodVisibilitySameAsParentRector::class, CallUserFuncArrayToVariadicRector::class, VersionCompareFuncCallToConstantRector::class]);
};
