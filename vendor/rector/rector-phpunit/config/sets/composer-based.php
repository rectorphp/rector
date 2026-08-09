<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use PHPStan\Type\MixedType;
use PHPStan\Type\VoidType;
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
use Rector\PHPUnit\PHPUnit100\Rector\Class_\AddProphecyTraitRector;
use Rector\PHPUnit\PHPUnit100\Rector\Class_\ParentTestClassConstructorRector;
use Rector\PHPUnit\PHPUnit100\Rector\Class_\PublicDataProviderClassMethodRector;
use Rector\PHPUnit\PHPUnit100\Rector\Class_\StaticDataProviderClassMethodRector;
use Rector\PHPUnit\PHPUnit100\Rector\MethodCall\PropertyExistsWithoutAssertRector;
use Rector\PHPUnit\PHPUnit100\Rector\MethodCall\RemoveSetMethodsMethodCallRector;
use Rector\PHPUnit\PHPUnit100\Rector\StmtsAwareInterface\WithConsecutiveRector;
use Rector\PHPUnit\PHPUnit110\Rector\CallLike\AssertContainsOnlyMethodCallRector;
use Rector\PHPUnit\PHPUnit110\Rector\Class_\NamedArgumentForDataProviderRector;
use Rector\PHPUnit\PHPUnit110\Rector\ClassMethod\ExpectsParamToMockObjectRector;
use Rector\PHPUnit\PHPUnit110\Rector\ClassMethod\MockObjectArgCreateStubToCreateMockRector;
use Rector\PHPUnit\PHPUnit120\Rector\Assign\AnyMatcherToNewAnyInvokedCountRector;
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
use Rector\PHPUnit\PHPUnit50\Rector\StaticCall\GetMockRector;
use Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\ExceptionAnnotationRector;
use Rector\PHPUnit\PHPUnit60\Rector\MethodCall\DelegateExceptionArgumentsRector;
use Rector\PHPUnit\PHPUnit60\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector;
use Rector\PHPUnit\PHPUnit70\Rector\Class_\RemoveDataProviderTestPrefixRector;
use Rector\PHPUnit\PHPUnit80\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;
use Rector\PHPUnit\PHPUnit80\Rector\MethodCall\SpecificAssertContainsRector;
use Rector\PHPUnit\PHPUnit80\Rector\MethodCall\SpecificAssertInternalTypeRector;
use Rector\PHPUnit\PHPUnit90\Rector\Class_\TestListenerToHooksRector;
use Rector\PHPUnit\PHPUnit90\Rector\MethodCall\AssertRegExpRector;
use Rector\PHPUnit\PHPUnit90\Rector\MethodCall\ExplicitPhpErrorApiRector;
use Rector\PHPUnit\PHPUnit90\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector;
use Rector\PHPUnit\ValueObject\AnnotationWithValueToAttribute;
use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameAnnotationByType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Rector\ValueObject\MethodName;
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
        // the any() matcher was deprecated in PHPUnit 12.5
        AnyMatcherToNewAnyInvokedCountRector::class,
        // the AllowMockObjectsWithoutExpectations attribute exists since PHPUnit 12.5.2
        AllowMockObjectsWhereParentClassRector::class,
        AllowMockObjectsForDataProviderRector::class,
        AllowMockObjectsWithoutExpectationsAttributeRector::class,
        // the upgrade rules of the per-version sets, each bound to the PHPUnit version
        // its target API is available from
        // expectException() and friends, PHPUnit 5.2
        ExceptionAnnotationRector::class,
        DelegateExceptionArgumentsRector::class,
        // createMock(), PHPUnit 5.4
        GetMockRector::class,
        GetMockBuilderGetMockToCreateMockRector::class,
        // PHPUnit 6.0
        AddDoesNotPerformAssertionToNonAssertingTestRector::class,
        // PHPUnit 7.0
        RemoveDataProviderTestPrefixRector::class,
        // the assertIsArray(), assertStringContainsString() and assertEqualsWithDelta() families, PHPUnit 7.5
        SpecificAssertInternalTypeRector::class,
        SpecificAssertContainsRector::class,
        AssertEqualsParameterToSpecificMethodsTypeRector::class,
        // the expectDeprecation() family, PHPUnit 8.4 until 10.0
        ExplicitPhpErrorApiRector::class,
        // PHPUnit 9.0, the hook interfaces until 10.0
        TestListenerToHooksRector::class,
        SpecificAssertContainsWithoutIdentityRector::class,
        // assertMatchesRegularExpression(), PHPUnit 9.1
        AssertRegExpRector::class,
        // PHPUnit 10.0
        StaticDataProviderClassMethodRector::class,
        PublicDataProviderClassMethodRector::class,
        AddProphecyTraitRector::class,
        WithConsecutiveRector::class,
        RemoveSetMethodsMethodCallRector::class,
        PropertyExistsWithoutAssertRector::class,
        ParentTestClassConstructorRector::class,
        // PHPUnit 11.0
        NamedArgumentForDataProviderRector::class,
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
    // the staticExpects() method was removed in PHPUnit 4.0
    // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/137
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('PHPUnit_Framework_MockObject_MockObject', 'staticExpects', 'expects')], 'phpunit/phpunit', '>=4.0');
    // the namespaced class names were introduced in PHPUnit 6.0
    // @see https://github.com/sebastianbergmann/phpunit/compare/5.7.9...6.0.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('PHPUnit\Framework\TestClass', 'setExpectedException', 'expectedException'), new MethodCallRename('PHPUnit\Framework\TestClass', 'setExpectedExceptionRegExp', 'expectedException'), new MethodCallRename('PHPUnit\Framework\TestCase', 'createMockBuilder', 'getMockBuilder')], 'phpunit/phpunit', '>=6.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // ref. https://github.com/sebastianbergmann/phpunit/compare/5.7.9...6.0.0
        'PHPUnit_Framework_MockObject_Stub' => 'PHPUnit\Framework\MockObject\Stub',
        'PHPUnit_Framework_MockObject_Stub_Return' => 'PHPUnit\Framework\MockObject\Stub\ReturnStub',
        'PHPUnit_Framework_MockObject_Matcher_Parameters' => 'PHPUnit\Framework\MockObject\Matcher\Parameters',
        'PHPUnit_Framework_MockObject_Matcher_Invocation' => 'PHPUnit\Framework\MockObject\Matcher\Invocation',
        'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\Framework\MockObject\MockObject',
        'PHPUnit_Framework_MockObject_Invocation_Object' => 'PHPUnit\Framework\MockObject\Invocation\ObjectInvocation',
        'PHPUnit_Framework_Assert' => 'PHPUnit\Framework\Assert',
        'PHPUnit_Framework_AssertionFailedError' => 'PHPUnit\Framework\AssertionFailedError',
        'PHPUnit_Framework_BaseTestListener' => 'PHPUnit\Framework\BaseTestListener',
        'PHPUnit_Framework_CodeCoverageException' => 'PHPUnit\Framework\CodeCoverageException',
        'PHPUnit_Exception' => 'PHPUnit\Exception',
        'PHPUnit_Framework_Exception' => 'PHPUnit\Framework\Exception',
        'PHPUnit_Framework_ExceptionWrapper' => 'PHPUnit\Framework\ExceptionWrapper',
        'PHPUnit_Framework_ExpectationFailedException' => 'PHPUnit\Framework\ExpectationFailedException',
        'PHPUnit_Framework_IncompleteTest' => 'PHPUnit\Framework\IncompleteTest',
        'PHPUnit_Framework_IncompleteTestCase' => 'PHPUnit\Framework\IncompleteTestCase',
        'PHPUnit_Framework_IncompleteTestError' => 'PHPUnit\Framework\IncompleteTestError',
        'PHPUnit_Framework_InvalidCoversTargetException' => 'PHPUnit\Framework\InvalidCoversTargetException',
        'PHPUnit_Framework_OutputError' => 'PHPUnit\Framework\OutputError',
        'PHPUnit_Framework_CoveredCodeNotExecutedException' => 'PHPUnit\Framework\CoveredCodeNotExecutedException',
        'PHPUnit_Framework_MissingCoversAnnotationException' => 'PHPUnit\Framework\MissingCoversAnnotationException',
        'PHPUnit_Framework_RiskyTest' => 'PHPUnit\Framework\RiskyTest',
        'PHPUnit_Framework_RiskyTestError' => 'PHPUnit\Framework\RiskyTestError',
        'PHPUnit_Framework_SkippedTest' => 'PHPUnit\Framework\SkippedTest',
        'PHPUnit_Framework_SkippedTestCase' => 'PHPUnit\Framework\SkippedTestCase',
        'PHPUnit_Framework_SkippedTestError' => 'PHPUnit\Framework\SkippedTestError',
        'PHPUnit_Framework_SkippedTestSuiteError' => 'PHPUnit\Framework\SkippedTestSuiteError',
        'PHPUnit_Framework_SyntheticError' => 'PHPUnit\Framework\SyntheticError',
        'PHPUnit_Framework_Test' => 'PHPUnit\Framework\Test',
        'PHPUnit_Framework_TestCase' => 'PHPUnit\Framework\TestCase',
        'PHPUnit_Framework_TestFailure' => 'PHPUnit\Framework\TestFailure',
        'PHPUnit_Framework_TestListener' => 'PHPUnit\Framework\TestListener',
        'PHPUnit_Framework_TestResult' => 'PHPUnit\Framework\TestResult',
        'PHPUnit_Framework_TestSuite' => 'PHPUnit\Framework\TestSuite',
        'PHPUnit_Framework_UnintentionallyCoveredCodeError' => 'PHPUnit\Framework\UnintentionallyCoveredCodeError',
        'PHPUnit_Framework_Warning' => 'PHPUnit\Framework\Warning',
        'PHPUnit_Framework_WarningTestCase' => 'PHPUnit\Framework\WarningTestCase',
        'PHPUnit_Framework_Constraint_And' => 'PHPUnit\Framework\Constraint\LogicalAnd',
        'PHPUnit_Framework_Constraint_ArrayHasKey' => 'PHPUnit\Framework\Constraint\ArrayHasKey',
        'PHPUnit_Framework_Constraint_ArraySubset' => 'PHPUnit\Framework\Constraint\ArraySubset',
        'PHPUnit_Framework_Constraint_Attribute' => 'PHPUnit\Framework\Constraint\Attribute',
        'PHPUnit_Framework_Constraint_Callback' => 'PHPUnit\Framework\Constraint\Callback',
        'PHPUnit_Framework_Constraint_ClassHasAttribute' => 'PHPUnit\Framework\Constraint\ClassHasAttribute',
        'PHPUnit_Framework_Constraint_Composite' => 'PHPUnit\Framework\Constraint\Composite',
        'PHPUnit_Framework_Constraint_Count' => 'PHPUnit\Framework\Constraint\Count',
        'PHPUnit_Framework_Constraint_DirectoryExists' => 'PHPUnit\Framework\Constraint\DirectoryExists',
        'PHPUnit_Framework_Constraint' => 'PHPUnit\Framework\Constraint\Constraint',
        'PHPUnit_Framework_Constraint_Exception' => 'PHPUnit\Framework\Constraint\Exception',
        'PHPUnit_Framework_Constraint_ExceptionCode' => 'PHPUnit\Framework\Constraint\ExceptionCode',
        'PHPUnit_Framework_Constraint_ExceptionMessage' => 'PHPUnit\Framework\Constraint\ExceptionMessage',
        'PHPUnit_Framework_Constraint_ExceptionMessageRegExp' => 'PHPUnit_Framework_Constraint_ExceptionMessageRegularExpression',
        'PHPUnit_Framework_Constraint_FileExists' => 'PHPUnit\Framework\Constraint\FileExists',
        'PHPUnit_Framework_Constraint_GreaterThan' => 'PHPUnit\Framework\Constraint\GreaterThan',
        'PHPUnit_Framework_Constraint_IsAnything' => 'PHPUnit\Framework\Constraint\IsAnything',
        'PHPUnit_Framework_Constraint_IsEmpty' => 'PHPUnit\Framework\Constraint\IsEmpty',
        'PHPUnit_Framework_Constraint_IsIdentical' => 'PHPUnit\Framework\Constraint\IsIdentical',
        'PHPUnit_Framework_Constraint_IsInfinite' => 'PHPUnit\Framework\Constraint\IsInfinite',
        'PHPUnit_Framework_Constraint_IsInstanceOf' => 'PHPUnit\Framework\Constraint\IsInstanceOf',
        'PHPUnit_Framework_Constraint_IsJson' => 'PHPUnit\Framework\Constraint\IsJson',
        'PHPUnit_Framework_Constraint_IsNan' => 'PHPUnit\Framework\Constraint\IsNan',
        'PHPUnit_Framework_Constraint_IsNull' => 'PHPUnit\Framework\Constraint\IsNull',
        'PHPUnit_Framework_Constraint_IsReadable' => 'PHPUnit\Framework\Constraint\IsReadable',
        'PHPUnit_Framework_Constraint_IsTrue' => 'PHPUnit\Framework\Constraint\IsTrue',
        'PHPUnit_Framework_Constraint_IsType' => 'PHPUnit\Framework\Constraint\IsType',
        'PHPUnit_Framework_Constraint_IsWritable' => 'PHPUnit\Framework\Constraint\IsWritable',
        'PHPUnit_Framework_Constraint_JsonMatches' => 'PHPUnit\Framework\Constraint\JsonMatches',
        'PHPUnit_Framework_Constraint_LessThan' => 'PHPUnit\Framework\Constraint\LessThan',
        'PHPUnit_Framework_Constraint_Not' => 'PHPUnit\Framework\Constraint\LogicalNot',
        'PHPUnit_Framework_Constraint_ObjectHasAttribute' => 'PHPUnit\Framework\Constraint\ObjectHasAttribute',
        'PHPUnit_Framework_Constraint_Or' => 'PHPUnit\Framework\Constraint\LogicalOr',
        'PHPUnit_Framework_Constraint_PCREMatch' => 'PHPUnit\Framework\Constraint\RegularExpression',
        'PHPUnit_Framework_Constraint_SameSize' => 'PHPUnit\Framework\Constraint\SameSize',
        'PHPUnit_Framework_Constraint_StringContains' => 'PHPUnit\Framework\Constraint\StringContains',
        'PHPUnit_Framework_Constraint_StringEndsWith' => 'PHPUnit\Framework\Constraint\StringEndsWith',
        'PHPUnit_Framework_Constraint_StringMatches' => 'PHPUnit\Framework\Constraint\StringMatchesFormatDescription',
        'PHPUnit_Framework_Constraint_StringStartsWith' => 'PHPUnit\Framework\Constraint\StringStartsWith',
        'PHPUnit_Framework_Constraint_TraversableContains' => 'PHPUnit\Framework\Constraint\TraversableContains',
        'PHPUnit_Framework_Constraint_TraversableContainsOnly' => 'PHPUnit\Framework\Constraint\TraversableContainsOnly',
        'PHPUnit_Framework_Constraint_Xor' => 'PHPUnit\Framework\Constraint\LogicalXor',
        'PHPUnit_Framework_Constraint_JsonMatches_ErrorMessageProvider' => 'PHPUnit\Framework\Constraint\JsonMatchesErrorMessageProvider',
        'PHPUnit_Framework_Error_Deprecated' => 'PHPUnit\Framework\Error\Deprecated',
        'PHPUnit_Framework_Error_Notice' => 'PHPUnit\Framework\Error\Notice',
        'PHPUnit_Framework_Error_Warning' => 'PHPUnit\Framework\Error\Warning',
        'PHPUnit_Framework_TestSuite_DataProvider' => 'PHPUnit\Framework\DataProviderTestSuite',
        'PHPUnit_Framework_Error' => 'PHPUnit\Framework\Error\Error',
        'PHPUnit_Runner_BaseTestRunner' => 'PHPUnit\Runner\BaseTestRunner',
        'PHPUnit_Runner_Exception' => 'PHPUnit\Runner\Exception',
        'PHPUnit_Runner_PhptTestCase' => 'PHPUnit\Runner\PhptTestCase',
        'PHPUnit_Runner_StandardTestSuiteLoader' => 'PHPUnit\Runner\StandardTestSuiteLoader',
        'PHPUnit_Runner_TestSuiteLoader' => 'PHPUnit\Runner\TestSuiteLoader',
        'PHPUnit_Runner_Version' => 'PHPUnit\Runner\Version',
        'PHPUnit_Runner_Filter_Factory' => 'PHPUnit\Runner\Filter\Factory',
        'PHPUnit_Runner_Filter_GroupFilterIterator' => 'PHPUnit\Runner\Filter\GroupFilterIterator',
        'PHPUnit_Runner_Filter_Test' => 'PHPUnit\Runner\Filter\NameFilterIterator',
        'PHPUnit_Runner_Filter_Group_Exclude' => 'PHPUnit\Runner\Filter\ExcludeGroupFilterIterator',
        'PHPUnit_Runner_Filter_Group_Include' => 'PHPUnit\Runner\Filter\IncludeGroupFilterIterator',
        'PHPUnit_TextUI_Command' => 'PHPUnit\TextUI\Command',
        'PHPUnit_TextUI_ResultPrinter' => 'PHPUnit\TextUI\ResultPrinter',
        'PHPUnit_TextUI_TestRunner' => 'PHPUnit\TextUI\TestRunner',
        'PHPUnit_Util_Blacklist' => 'PHPUnit\Util\Blacklist',
        'PHPUnit_Util_Configuration' => 'PHPUnit\Util\Configuration',
        'PHPUnit_Util_ConfigurationGenerator' => 'PHPUnit\Util\ConfigurationGenerator',
        'PHPUnit_Util_ErrorHandler' => 'PHPUnit\Util\ErrorHandler',
        'PHPUnit_Util_Fileloader' => 'PHPUnit\Util\Fileloader',
        'PHPUnit_Util_Filesystem' => 'PHPUnit\Util\Filesystem',
        'PHPUnit_Util_Filter' => 'PHPUnit\Util\Filter',
        'PHPUnit_Util_Getopt' => 'PHPUnit\Util\Getopt',
        'PHPUnit_Util_GlobalState' => 'PHPUnit\Util\GlobalState',
        'PHPUnit_Util_InvalidArgumentHelper' => 'PHPUnit\Util\InvalidArgumentHelper',
        'PHPUnit_Util_PHP' => 'PHPUnit\Util\PHP\AbstractPhpProcess',
        'PHPUnit_Util_PHP_Default' => 'PHPUnit\Util\PHP\DefaultPhpProcess',
        'PHPUnit_Util_PHP_Windows' => 'PHPUnit\Util\PHP\WindowsPhpProcesPhpProcess',
        'PHPUnit_Util_Log_JUnit' => 'PHPUnit\Util\Log\JUnit',
        'PHPUnit_Util_Log_TeamCity' => 'PHPUnit\Util\Log\TeamCity',
        'PHPUnit_Util_TestDox_NamePrettifier' => 'PHPUnit\Util\TestDox\NamePrettifier',
        'PHPUnit_Util_TestDox_ResultPrinter' => 'PHPUnit\Util\TestDox\ResultPrinter',
        'PHPUnit_Util_TestDox_ResultPrinter_HTML' => 'PHPUnit\Util\TestDox\HtmlResultPrinter',
        'PHPUnit_Util_TestDox_ResultPrinter_Text' => 'PHPUnit\Util\TestDox\TextResultPrinter',
        'PHPUnit_Util_TestDox_ResultPrinter_XML' => 'PHPUnit\Util\TestDox\XmlResultPrinter',
        'PHPUnit_Util_Printer' => 'PHPUnit\Util\Printer',
        'PHPUnit_Util_Regex' => 'PHPUnit\Util\RegularExpression',
        'PHPUnit_Util_String' => 'PHPUnit\Util\Utf8',
        'PHPUnit_Util_XML' => 'PHPUnit\Util\XML',
        'PHPUnit_Util_TestSuiteIterator' => 'PHPUnit\Framework\TestSuiteIterator',
        'PHPUnit_Util_Type' => 'PHPUnit\Util\Type',
        'PHPUnit_Util_Test' => 'PHPUnit\Util\Test',
    ], 'phpunit/phpunit', '>=6.0');
    // the "scenario" annotation was dropped in PHPUnit 7.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameAnnotationRector::class, [new RenameAnnotationByType('PHPUnit\Framework\TestCase', 'scenario', 'test')], 'phpunit/phpunit', '>=7.0');
    // the TestCase methods were given native types in PHPUnit 8.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [
        // @see https://github.com/rectorphp/rector/issues/1024 - no type, $dataName
        new AddParamTypeDeclaration('PHPUnit\Framework\TestCase', MethodName::CONSTRUCT, 2, new MixedType()),
    ], 'phpunit/phpunit', '>=8.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'setUpBeforeClass', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'setUp', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'assertPreConditions', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'assertPostConditions', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'tearDown', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'tearDownAfterClass', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'onNotSuccessfulTest', new VoidType())], 'phpunit/phpunit', '>=8.0');
    // the expectDeprecationMessage(), expectNoticeMessage(), expectWarningMessage() and expectErrorMessage()
    // methods were removed in PHPUnit 10.0
    // @see https://github.com/sebastianbergmann/phpunit/issues/5062
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('PHPUnit\Framework\TestCase', 'expectDeprecationMessage', 'expectExceptionMessage'), new MethodCallRename('PHPUnit\Framework\TestCase', 'expectDeprecationMessageMatches', 'expectExceptionMessageMatches'), new MethodCallRename('PHPUnit\Framework\TestCase', 'expectNoticeMessage', 'expectExceptionMessage'), new MethodCallRename('PHPUnit\Framework\TestCase', 'expectNoticeMessageMatches', 'expectExceptionMessageMatches'), new MethodCallRename('PHPUnit\Framework\TestCase', 'expectWarningMessage', 'expectExceptionMessage'), new MethodCallRename('PHPUnit\Framework\TestCase', 'expectWarningMessageMatches', 'expectExceptionMessageMatches'), new MethodCallRename('PHPUnit\Framework\TestCase', 'expectErrorMessage', 'expectExceptionMessage'), new MethodCallRename('PHPUnit\Framework\TestCase', 'expectErrorMessageMatches', 'expectExceptionMessageMatches')], 'phpunit/phpunit', '>=10.0');
    // the attributes replacing these annotations were all added in PHPUnit 10.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationWithValueToAttributeRector::class, [new AnnotationWithValueToAttribute('backupGlobals', 'PHPUnit\Framework\Attributes\BackupGlobals', ['enabled' => \true, 'disabled' => \false]), new AnnotationWithValueToAttribute('backupStaticAttributes', 'PHPUnit\Framework\Attributes\BackupStaticProperties', ['enabled' => \true, 'disabled' => \false]), new AnnotationWithValueToAttribute('preserveGlobalState', 'PHPUnit\Framework\Attributes\PreserveGlobalState', ['enabled' => \true, 'disabled' => \false]), new AnnotationWithValueToAttribute('depends', 'PHPUnit\Framework\Attributes\Depends'), new AnnotationWithValueToAttribute('group', 'PHPUnit\Framework\Attributes\Group'), new AnnotationWithValueToAttribute('uses', 'PHPUnit\Framework\Attributes\UsesClass', [], \true), new AnnotationWithValueToAttribute('testDox', 'PHPUnit\Framework\Attributes\TestDox'), new AnnotationWithValueToAttribute('testdox', 'PHPUnit\Framework\Attributes\TestDox')], 'phpunit/phpunit', '>=10.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationToAttributeRector::class, [
        // @see https://github.com/sebastianbergmann/phpunit/issues/4502
        new AnnotationToAttribute('after', 'PHPUnit\Framework\Attributes\After'),
        new AnnotationToAttribute('afterClass', 'PHPUnit\Framework\Attributes\AfterClass'),
        new AnnotationToAttribute('before', 'PHPUnit\Framework\Attributes\Before'),
        new AnnotationToAttribute('beforeClass', 'PHPUnit\Framework\Attributes\BeforeClass'),
        // CodeCoverageIgnore attribute is deprecated.
        // @see https://github.com/rectorphp/rector-phpunit/pull/304
        // new AnnotationToAttribute('codeCoverageIgnore', 'PHPUnit\Framework\Attributes\CodeCoverageIgnore'),
        new AnnotationToAttribute('coversNothing', 'PHPUnit\Framework\Attributes\CoversNothing'),
        new AnnotationToAttribute('doesNotPerformAssertions', 'PHPUnit\Framework\Attributes\DoesNotPerformAssertions'),
        new AnnotationToAttribute('large', 'PHPUnit\Framework\Attributes\Large'),
        new AnnotationToAttribute('medium', 'PHPUnit\Framework\Attributes\Medium'),
        new AnnotationToAttribute('preCondition', 'PHPUnit\Framework\Attributes\PreCondition'),
        new AnnotationToAttribute('postCondition', 'PHPUnit\Framework\Attributes\PostCondition'),
        new AnnotationToAttribute('runInSeparateProcess', 'PHPUnit\Framework\Attributes\RunInSeparateProcess'),
        new AnnotationToAttribute('runTestsInSeparateProcesses', 'PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses'),
        new AnnotationToAttribute('small', 'PHPUnit\Framework\Attributes\Small'),
        new AnnotationToAttribute('test', 'PHPUnit\Framework\Attributes\Test'),
    ], 'phpunit/phpunit', '>=10.0');
};
