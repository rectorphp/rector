<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\MagicDisclosure\Rector\ClassMethod\ReturnThisRemoveRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

final class SkipContainerConfigurator
{
    public function run(ContainerConfigurator $containerConfigurator)
    {
        $services = $containerConfigurator->services();

        $services->set(ReturnThisRemoveRector::class)
            ->arg('key', 'value');
    }
}
