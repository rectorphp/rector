<?php

declare(strict_types=1);

use Rector\Core\Rector\Namespace_\PseudoNamespaceToNamespaceRector;
use Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector;
use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/phpunit-exception.php');

    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->arg('$oldToNewMethodsByClass', [
            'PHPUnit\Framework\TestCase' => [
                'createMockBuilder' => 'getMockBuilder',
            ],
        ]);

    $services->set(RenameClassRector::class)
        ->arg('$oldToNewClasses', [
            'PHPUnit_Framework_MockObject_Stub' => 'PHPUnit\Framework\MockObject\Stub',
            'PHPUnit_Framework_MockObject_Stub_Return' => 'PHPUnit\Framework\MockObject\Stub\ReturnStub',
            'PHPUnit_Framework_MockObject_Matcher_Parameters' => 'PHPUnit\Framework\MockObject\Matcher\Parameters',
            'PHPUnit_Framework_MockObject_Matcher_Invocation' => 'PHPUnit\Framework\MockObject\Matcher\Invocation',
            'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\Framework\MockObject\MockObject',
            'PHPUnit_Framework_MockObject_Invocation_Object' => 'PHPUnit\Framework\MockObject\Invocation\ObjectInvocation',
        ]);

    $services->set(PseudoNamespaceToNamespaceRector::class)
        ->arg('$namespacePrefixesWithExcludedClasses', [
            'PHPUnit_' => [
                # ref. https://github.com/sebastianbergmann/phpunit/compare/5.7.9...6.0.0
                'PHPUnit_Framework_MockObject_MockObject',
                'PHPUnit_Framework_MockObject_Invocation_Object',
                'PHPUnit_Framework_MockObject_Matcher_Invocation',
                'PHPUnit_Framework_MockObject_Matcher_Parameters',
                'PHPUnit_Framework_MockObject_Stub_Return',
                'PHPUnit_Framework_MockObject_Stub',
            ],
        ]);

    $services->set(AddDoesNotPerformAssertionToNonAssertingTestRector::class);

    $services->set(GetMockBuilderGetMockToCreateMockRector::class);
};
