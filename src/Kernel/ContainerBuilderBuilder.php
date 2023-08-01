<?php

declare (strict_types=1);
namespace Rector\Core\Kernel;

use Rector\Core\Config\Loader\ConfigureCallMergingLoaderFactory;
use Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector;
use Rector\Core\DependencyInjection\CompilerPass\MergeImportedRectorConfigureCallValuesCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\RemoveSkippedRectorsCompilerPass;
use RectorPrefix202308\Symfony\Component\DependencyInjection\ContainerBuilder;
final class ContainerBuilderBuilder
{
    /**
     * @param string[] $configFiles
     */
    public function build(array $configFiles) : ContainerBuilder
    {
        $configureCallValuesCollector = new ConfigureCallValuesCollector();
        $configureCallMergingLoaderFactory = new ConfigureCallMergingLoaderFactory($configureCallValuesCollector);
        $containerBuilderFactory = new \Rector\Core\Kernel\ContainerBuilderFactory($configureCallMergingLoaderFactory);
        $containerBuilder = $containerBuilderFactory->create($configFiles, [
            new RemoveSkippedRectorsCompilerPass(),
            // adds all merged configure() parameters to rector services
            new MergeImportedRectorConfigureCallValuesCompilerPass($configureCallValuesCollector),
        ]);
        $containerBuilder->compile();
        return $containerBuilder;
    }
}
