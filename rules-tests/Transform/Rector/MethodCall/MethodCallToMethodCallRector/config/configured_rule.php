<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\FirstDependency;
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\SecondDependency;
use Rector\Transform\Rector\MethodCall\MethodCallToMethodCallRector;
use Rector\Transform\ValueObject\MethodCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(MethodCallToMethodCallRector::class)
        ->call('configure', [[
            MethodCallToMethodCallRector::METHOD_CALLS_TO_METHOD_CALLS => ValueObjectInliner::inline([
                new MethodCallToMethodCall(FirstDependency::class, 'go', SecondDependency::class, 'away'),
            ]),
        ]]);
};
