<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Rector\ClassMethod\ReturnThisRemoveRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

final class SkipReturn
{
    public function run(ContainerConfigurator $containerConfigurator)
    {
        $services = $containerConfigurator->services();
        return $services->set(ReturnThisRemoveRector::class)
            ->arg('key', 'value');
    }
}
