<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\PHPUnit\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector;
use Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector;
use Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveExpectAnyFromMockRector::class);
    $services->set(AddSeeTestAnnotationRector::class);
    $services->set(ReturnArrayClassMethodToYieldRector::class);
    $services->set(ConstructClassMethodToSetUpTestCaseRector::class);
    $services->set(AssertSameTrueFalseToAssertTrueFalseRector::class);
};
