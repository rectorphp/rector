<?php

declare (strict_types=1);
namespace RectorPrefix20211123;

use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\PHPUnit\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector;
use Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector;
use Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector::class);
    $services->set(\Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector::class);
    $services->set(\Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector::class)->call('configure', [[\Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector::METHODS_TO_YIELDS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'provide*'), new \Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', 'dataProvider*')])]]);
    $services->set(\Rector\PHPUnit\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector::class);
    $services->set(\Rector\PHPUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector::class);
};
