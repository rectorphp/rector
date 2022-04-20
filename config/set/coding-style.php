<?php

declare(strict_types=1);

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

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(PHPStormVarAnnotationRector::class);
    $rectorConfig->rule(NullableCompareToNullRector::class);
    $rectorConfig->rule(BinarySwitchToIfElseRector::class);
    $rectorConfig->rule(ConsistentImplodeRector::class);
    $rectorConfig->rule(TernaryConditionVariableAssignmentRector::class);
    $rectorConfig->rule(SymplifyQuoteEscapeRector::class);
    $rectorConfig->rule(SplitGroupedConstantsAndPropertiesRector::class);
    $rectorConfig->rule(StringClassNameToClassConstantRector::class);
    $rectorConfig->rule(ConsistentPregDelimiterRector::class);
    $rectorConfig->rule(CatchExceptionNameMatchingTypeRector::class);
    $rectorConfig->rule(UseIncrementAssignRector::class);
    $rectorConfig->rule(SplitDoubleAssignRector::class);
    $rectorConfig->rule(VarConstantCommentRector::class);
    $rectorConfig->rule(EncapsedStringsToSprintfRector::class);
    $rectorConfig->rule(WrapEncapsedVariableInCurlyBracesRector::class);
    $rectorConfig->rule(NewlineBeforeNewAssignSetRector::class);
    $rectorConfig->rule(AddArrayDefaultToArrayPropertyRector::class);
    $rectorConfig->rule(AddFalseDefaultToBoolPropertyRector::class);
    $rectorConfig->rule(MakeInheritedMethodVisibilitySameAsParentRector::class);
    $rectorConfig->rule(CallUserFuncArrayToVariadicRector::class);
    $rectorConfig->rule(VersionCompareFuncCallToConstantRector::class);

    $rectorConfig
        ->ruleWithConfiguration(FuncCallToConstFetchRector::class, [
            'php_sapi_name' => 'PHP_SAPI',
            'pi' => 'M_PI',
        ]);

    $rectorConfig->rule(SeparateMultiUseImportsRector::class);
    $rectorConfig->rule(RemoveDoubleUnderscoreInMethodNameRector::class);
    $rectorConfig->rule(PostIncDecToPreIncDecRector::class);
    $rectorConfig->rule(UnSpreadOperatorRector::class);
    $rectorConfig->rule(NewlineAfterStatementRector::class);
    $rectorConfig->rule(RemoveFinalFromConstRector::class);
};
