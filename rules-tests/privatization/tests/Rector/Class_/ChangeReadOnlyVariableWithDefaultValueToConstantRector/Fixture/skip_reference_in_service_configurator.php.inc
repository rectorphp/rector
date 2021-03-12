<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class SkipReferenceInSerivceConfigurator
{
    public function run()
    {
        $containerBuilder = $this->createContainerBuilder();
        $phpFileLoader = $this->createPhpFileLoader($containerBuilder);

        $instanceOf = [];
        return new ServicesConfigurator($containerBuilder, $phpFileLoader, $instanceOf);
    }

    private function createPhpFileLoader(ContainerBuilder $containerBuilder): PhpFileLoader
    {
        return new PhpFileLoader($containerBuilder, new FileLocator());
    }

    private function createContainerBuilder(): ContainerBuilder
    {
        return new ContainerBuilder();
    }
}
