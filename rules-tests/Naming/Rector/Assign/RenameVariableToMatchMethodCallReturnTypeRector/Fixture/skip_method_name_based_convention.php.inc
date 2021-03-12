<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class SkipMethodNameBasedConvention
{
    public function run()
    {
        $containerConfigurator = new ContainerConfigurator();
        $services = $containerConfigurator->services();
    }
}
