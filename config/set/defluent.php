<?php

declare(strict_types=1);

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

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // variable/property
    $services->set(FluentChainMethodCallToNormalMethodCallRector::class);
    $services->set(ReturnFluentChainMethodCallToNormalMethodCallRector::class);

    // new
    $services->set(NewFluentChainMethodCallToNonFluentRector::class);
    $services->set(ReturnNewFluentChainMethodCallToNonFluentRector::class);

    $services->set(ReturnThisRemoveRector::class);
    $services->set(DefluentReturnMethodCallRector::class);
    $services->set(MethodCallOnSetterMethodCallToStandaloneAssignRector::class);
    $services->set(InArgFluentChainMethodCallToStandaloneMethodCallRector::class);
};
