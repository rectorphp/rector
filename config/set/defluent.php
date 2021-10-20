<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Defluent\Rector\ClassMethod\ReturnThisRemoveRector;
use Rector\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector;
use Rector\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector;
use Rector\Defluent\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector;
use Rector\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector;
use Rector\Defluent\Rector\Return_\DefluentReturnMethodCallRector;
use Rector\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector;
use Rector\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
// @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/
// @see https://www.yegor256.com/2018/03/13/fluent-interfaces.html
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    // variable/property
    $services->set(\Rector\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector::class);
    $services->set(\Rector\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector::class);
    // new
    $services->set(\Rector\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector::class);
    $services->set(\Rector\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector::class);
    $services->set(\Rector\Defluent\Rector\ClassMethod\ReturnThisRemoveRector::class);
    $services->set(\Rector\Defluent\Rector\Return_\DefluentReturnMethodCallRector::class);
    $services->set(\Rector\Defluent\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector::class);
    $services->set(\Rector\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector::class);
};
