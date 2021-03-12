<?php

namespace Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Rector\ClassMethod\ReturnThisRemoveRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

final class SkipContainerConfigurator
{
    public function run(ContainerConfigurator $containerConfigurator)
    {
        $services = $containerConfigurator->services();
        return $services->set(ReturnThisRemoveRector::class)
            ->arg('key', 'value');
    }
}
