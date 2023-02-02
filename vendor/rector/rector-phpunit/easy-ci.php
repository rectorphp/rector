<?php

declare (strict_types=1);
namespace RectorPrefix202302;

use Rector\PHPUnit\Rector\Class_\ArrayArgumentToDataProviderRector;
use Rector\PHPUnit\Rector\Class_\ProphecyPHPDocRector;
use Rector\PHPUnit\Rector\ClassMethod\CreateMockToAnonymousClassRector;
use Rector\PHPUnit\Rector\ClassMethod\RemoveEmptyTestMethodRector;
use Rector\PHPUnit\Rector\ClassMethod\ReplaceTestAnnotationWithPrefixedFunctionRector;
use Rector\PHPUnit\Rector\ClassMethod\TryCatchToExpectExceptionRector;
use Rector\PHPUnit\Rector\MethodCall\AssertResourceToClosedResourceRector;
use Rector\PHPUnit\Rector\MethodCall\CreateMockToCreateStubRector;
use Rector\PHPUnit\Rector\MethodCall\UseSpecificWithMethodRector;
use RectorPrefix202302\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->paths([__DIR__ . '/config', __DIR__ . '/src']);
    $easyCIConfig->typesToSkip([CreateMockToAnonymousClassRector::class, RemoveEmptyTestMethodRector::class, ReplaceTestAnnotationWithPrefixedFunctionRector::class, TryCatchToExpectExceptionRector::class, ArrayArgumentToDataProviderRector::class, ProphecyPHPDocRector::class, AssertResourceToClosedResourceRector::class, CreateMockToCreateStubRector::class, UseSpecificWithMethodRector::class]);
};
