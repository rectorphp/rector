<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector;
use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector;
use Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncCallToVariadicRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector;
use Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
use Rector\CodingStyle\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector;
use Rector\CodingStyle\Rector\Identical\IdenticalFalseToBooleanNotRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector;
use Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector;
use Rector\CodingStyle\Rector\PropertyProperty\UnderscoreToCamelCasePropertyNameRector;
use Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector;
use Rector\CodingStyle\Rector\Ternary\TernaryConditionVariableAssignmentRector;
use Rector\CodingStyle\Rector\Use_\RemoveUnusedAliasRector;
use Rector\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector;
use Rector\CodingStyle\Rector\Variable\UnderscoreToCamelCaseVariableNameRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NullableCompareToNullRector::class);

    $services->set(IdenticalFalseToBooleanNotRector::class);

    $services->set(BinarySwitchToIfElseRector::class);

    $services->set(ConsistentImplodeRector::class);

    $services->set(TernaryConditionVariableAssignmentRector::class);

    $services->set(RemoveUnusedAliasRector::class);

    $services->set(SymplifyQuoteEscapeRector::class);

    $services->set(SplitGroupedConstantsAndPropertiesRector::class);

    $services->set(SplitStringClassConstantToClassConstFetchRector::class);

    $services->set(StringClassNameToClassConstantRector::class);

    $services->set(ConsistentPregDelimiterRector::class);

    $services->set(FollowRequireByDirRector::class);

    $services->set(CatchExceptionNameMatchingTypeRector::class);

    $services->set(UseIncrementAssignRector::class);

    $services->set(SplitDoubleAssignRector::class);

    $services->set(VarConstantCommentRector::class);

    $services->set(EncapsedStringsToSprintfRector::class);

    $services->set(WrapEncapsedVariableInCurlyBracesRector::class);

    $services->set(NewlineBeforeNewAssignSetRector::class);

    $services->set(ManualJsonStringToJsonEncodeArrayRector::class);

    $services->set(AddArrayDefaultToArrayPropertyRector::class);

    $services->set(MakeInheritedMethodVisibilitySameAsParentRector::class);

    $services->set(CallUserFuncCallToVariadicRector::class);

    $services->set(VersionCompareFuncCallToConstantRector::class);

    $services->set(FunctionCallToConstantRector::class)
        ->call('configure', [[
            FunctionCallToConstantRector::FUNCTIONS_TO_CONSTANTS => [
                'php_sapi_name' => 'PHP_SAPI',
                'pi' => 'M_PI',
            ],
        ]]);

    $services->set(CamelCaseFunctionNamingToUnderscoreRector::class);

    $services->set(SplitGroupedUseImportsRector::class);

    $services->set(UnderscoreToCamelCasePropertyNameRector::class);

    $services->set(UnderscoreToCamelCaseVariableNameRector::class);

    $services->set(RemoveDoubleUnderscoreInMethodNameRector::class);
};
