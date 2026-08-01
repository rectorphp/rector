<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\AnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\CoversAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\RequiresAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\TicketAnnotationToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DependsAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\TestWithAnnotationToAttributeRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\AddIntersectionParamToMockObjectParamRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\AddIntersectionVarToMockObjectPropertyRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\AddStubIntersectionVarToStubPropertyRector;
use Rector\PHPUnit\CodeQuality\Rector\ClassMethod\BareCreateMockAssignToDirectUseRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\RemoveExpectAnyFromMockRector;
use Rector\PHPUnit\PHPUnit110\Rector\CallLike\AssertContainsOnlyMethodCallRector;
use Rector\PHPUnit\PHPUnit110\Rector\ClassMethod\ExpectsParamToMockObjectRector;
use Rector\PHPUnit\PHPUnit110\Rector\ClassMethod\MockObjectArgCreateStubToCreateMockRector;
use Rector\PHPUnit\PHPUnit120\Rector\CallLike\CreateStubInCoalesceArgRector;
use Rector\PHPUnit\PHPUnit120\Rector\CallLike\CreateStubOverCreateMockArgRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AllowMockObjectsForDataProviderRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AllowMockObjectsWhereParentClassRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AllowMockObjectsWithoutExpectationsAttributeRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AssertIsTypeMethodCallRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\PropertyCreateMockToCreateStubRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\RemoveOverrideFinalConstructTestCaseRector;
use Rector\PHPUnit\PHPUnit120\Rector\ClassMethod\ExpressionCreateMockToCreateStubRector;
use Rector\PHPUnit\PHPUnit120\Rector\Property\MockObjectVarToStubRector;
use Rector\PHPUnit\ValueObject\AnnotationWithValueToAttribute;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
/**
 * Rules and configuration bound to the PHPUnit version installed in the analysed project,
 * as not every attribute and method exists in every PHPUnit version.
 *
 * Thanks to the composer package constraint, these rules can be registered once here, instead of being
 * repeated in every PHPUnit version set to cover a direct upgrade from an older version.
 */
return static function (RectorConfig $rectorConfig): void {
    // each of these rules declares the "phpunit/phpunit" version its attributes were added in,
    // @see ComposerPackageConstraintInterface
    $rectorConfig->rules([
        TicketAnnotationToAttributeRector::class,
        TestWithAnnotationToAttributeRector::class,
        DataProviderAnnotationToAttributeRector::class,
        CoversAnnotationWithValueToAttributeRector::class,
        RequiresAnnotationWithValueToAttributeRector::class,
        DependsAnnotationWithValueToAttributeRector::class,
        // stubs over mocks, where no expectations are set, since PHPUnit 11.0
        CreateStubOverCreateMockArgRector::class,
        CreateStubInCoalesceArgRector::class,
        ExpressionCreateMockToCreateStubRector::class,
        PropertyCreateMockToCreateStubRector::class,
        MockObjectVarToStubRector::class,
        AddIntersectionVarToMockObjectPropertyRector::class,
        AddStubIntersectionVarToStubPropertyRector::class,
        BareCreateMockAssignToDirectUseRector::class,
        RemoveExpectAnyFromMockRector::class,
        // mocks back over stubs, where a mock object is required
        MockObjectArgCreateStubToCreateMockRector::class,
        ExpectsParamToMockObjectRector::class,
        AddIntersectionParamToMockObjectParamRector::class,
        // deprecated in PHPUnit 11.5
        AssertContainsOnlyMethodCallRector::class,
        AssertIsTypeMethodCallRector::class,
        // the TestCase::__construct() is final since PHPUnit 12.0.3
        RemoveOverrideFinalConstructTestCaseRector::class,
        // the AllowMockObjectsWithoutExpectations attribute exists since PHPUnit 12.5.2
        AllowMockObjectsWhereParentClassRector::class,
        AllowMockObjectsForDataProviderRector::class,
        AllowMockObjectsWithoutExpectationsAttributeRector::class,
    ]);
    // both attributes were added in PHPUnit 10.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationWithValueToAttributeRector::class, [
        // the PHPUnit 10 spelling of the "backupStaticAttributes" annotation
        new AnnotationWithValueToAttribute('backupStaticProperties', 'PHPUnit\Framework\Attributes\BackupStaticProperties', ['enabled' => \true, 'disabled' => \false]),
        new AnnotationWithValueToAttribute('excludeGlobalVariableFromBackup', 'PHPUnit\Framework\Attributes\ExcludeGlobalVariableFromBackup'),
    ], 'phpunit/phpunit', '>=10.0');
    // the RunClassInSeparateProcess attribute was added in PHPUnit 10.0 and removed in PHPUnit 13.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationToAttributeRector::class, [new AnnotationToAttribute('runClassInSeparateProcess', 'PHPUnit\Framework\Attributes\RunClassInSeparateProcess')], 'phpunit/phpunit', '>=10.0 <13.0');
    // the MockBuilder::onlyMethods() method was added in PHPUnit 8.3
    // @see https://github.com/sebastianbergmann/phpunit/pull/3687
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('PHPUnit\Framework\MockObject\MockBuilder', 'setMethods', 'onlyMethods')], 'phpunit/phpunit', '>=8.3');
    // the expectExceptionMessageMatches() method was added in PHPUnit 8.4
    // @see https://github.com/sebastianbergmann/phpunit/issues/3957
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('PHPUnit\Framework\TestCase', 'expectExceptionMessageRegExp', 'expectExceptionMessageMatches')], 'phpunit/phpunit', '>=8.4');
    // all these assert methods were added in PHPUnit 9.1
    // @see https://github.com/sebastianbergmann/phpunit/blob/9.1.0/ChangeLog-9.1.md
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('PHPUnit\Framework\Assert', 'assertRegExp', 'assertMatchesRegularExpression'), new MethodCallRename('PHPUnit\Framework\Assert', 'assertNotRegExp', 'assertDoesNotMatchRegularExpression'), new MethodCallRename('PHPUnit\Framework\Assert', 'assertFileNotExists', 'assertFileDoesNotExist'), new MethodCallRename('PHPUnit\Framework\Assert', 'assertFileNotIsReadable', 'assertFileIsNotReadable'), new MethodCallRename('PHPUnit\Framework\Assert', 'assertDirectoryNotExists', 'assertDirectoryDoesNotExist'), new MethodCallRename('PHPUnit\Framework\Assert', 'assertDirectoryNotIsReadable', 'assertDirectoryIsNotReadable'), new MethodCallRename('PHPUnit\Framework\Assert', 'assertDirectoryNotIsWritable', 'assertDirectoryIsNotWritable'), new MethodCallRename('PHPUnit\Framework\Assert', 'assertNotIsReadable', 'assertIsNotReadable'), new MethodCallRename('PHPUnit\Framework\Assert', 'assertNotIsWritable', 'assertIsNotWritable')], 'phpunit/phpunit', '>=9.1');
    // the numberOfInvocations() method was added in PHPUnit 10.0
    // @see https://github.com/sebastianbergmann/phpunit/commit/2ba8b7fded44a1a75cf5712a3b7310a8de0b6bb8
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('PHPUnit\Framework\MockObject\Rule\InvocationOrder', 'getInvocationCount', 'numberOfInvocations')], 'phpunit/phpunit', '>=10.0');
    // the assertObjectHasProperty() and assertObjectNotHasProperty() methods were added in PHPUnit 10.1
    // @see https://github.com/sebastianbergmann/phpunit/issues/5220
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('PHPUnit\Framework\Assert', 'assertObjectHasAttribute', 'assertObjectHasProperty'), new MethodCallRename('PHPUnit\Framework\Assert', 'assertObjectNotHasAttribute', 'assertObjectNotHasProperty')], 'phpunit/phpunit', '>=10.1');
    // the expectExceptionMessageIsOrContains() method was added in PHPUnit 13.2
    // @see https://github.com/sebastianbergmann/phpunit/issues/6560
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('PHPUnit\Framework\TestCase', 'expectExceptionMessage', 'expectExceptionMessageIsOrContains')], 'phpunit/phpunit', '>=13.2');
};
