<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector;
use Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/phpunit-exception.php');
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('PHPUnit\\Framework\\TestCase', 'createMockBuilder', 'getMockBuilder')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['PHPUnit_Framework_MockObject_Stub' => 'PHPUnit\\Framework\\MockObject\\Stub', 'PHPUnit_Framework_MockObject_Stub_Return' => 'PHPUnit\\Framework\\MockObject\\Stub\\ReturnStub', 'PHPUnit_Framework_MockObject_Matcher_Parameters' => 'PHPUnit\\Framework\\MockObject\\Matcher\\Parameters', 'PHPUnit_Framework_MockObject_Matcher_Invocation' => 'PHPUnit\\Framework\\MockObject\\Matcher\\Invocation', 'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\\Framework\\MockObject\\MockObject', 'PHPUnit_Framework_MockObject_Invocation_Object' => 'PHPUnit\\Framework\\MockObject\\Invocation\\ObjectInvocation']);
    $rectorConfig->ruleWithConfiguration(PseudoNamespaceToNamespaceRector::class, [
        // ref. https://github.com/sebastianbergmann/phpunit/compare/5.7.9...6.0.0
        new PseudoNamespaceToNamespace('PHPUnit_', ['PHPUnit_Framework_MockObject_MockObject', 'PHPUnit_Framework_MockObject_Invocation_Object', 'PHPUnit_Framework_MockObject_Matcher_Invocation', 'PHPUnit_Framework_MockObject_Matcher_Parameters', 'PHPUnit_Framework_MockObject_Stub_Return', 'PHPUnit_Framework_MockObject_Stub']),
    ]);
    $rectorConfig->rule(AddDoesNotPerformAssertionToNonAssertingTestRector::class);
    $rectorConfig->rule(GetMockBuilderGetMockToCreateMockRector::class);
};
