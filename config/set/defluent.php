<?php

declare(strict_types=1);

use Rector\MagicDisclosure\Rector\ClassMethod\ReturnThisRemoveRector;
use Rector\MagicDisclosure\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector;
use Rector\MagicDisclosure\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector;
use Rector\MagicDisclosure\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector;
use Rector\MagicDisclosure\Rector\Return_\DefluentReturnMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

// @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/
// @see https://www.yegor256.com/2018/03/13/fluent-interfaces.html

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReturnThisRemoveRector::class);

    $services->set(DefluentReturnMethodCallRector::class);

    $services->set(FluentChainMethodCallToNormalMethodCallRector::class);

    $services->set(MethodCallOnSetterMethodCallToStandaloneAssignRector::class);

    $services->set(InArgFluentChainMethodCallToStandaloneMethodCallRector::class);
};
