<?php

declare (strict_types=1);
namespace Rector\DependencyInjection;

use RectorPrefix202402\Illuminate\Container\Container;
use Rector\Autoloading\BootstrapFilesIncluder;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\ValueObject\Bootstrap\BootstrapConfigs;
final class RectorContainerFactory
{
    public function createFromBootstrapConfigs(BootstrapConfigs $bootstrapConfigs) : Container
    {
        $container = $this->createFromConfigs($bootstrapConfigs->getConfigFiles());
        $mainConfigFile = $bootstrapConfigs->getMainConfigFile();
        if ($mainConfigFile !== null) {
            /** @var ChangedFilesDetector $changedFilesDetector */
            $changedFilesDetector = $container->make(ChangedFilesDetector::class);
            $changedFilesDetector->setFirstResolvedConfigFileInfo($mainConfigFile);
        }
        /** @var BootstrapFilesIncluder $bootstrapFilesIncluder */
        $bootstrapFilesIncluder = $container->get(BootstrapFilesIncluder::class);
        $bootstrapFilesIncluder->includeBootstrapFiles();
        return $container;
    }
    /**
     * @param string[] $configFiles
     */
    private function createFromConfigs(array $configFiles) : Container
    {
        $lazyContainerFactory = new \Rector\DependencyInjection\LazyContainerFactory();
        $container = $lazyContainerFactory->create();
        foreach ($configFiles as $configFile) {
            $container->import($configFile);
        }
        $container->boot();
        return $container;
    }
}
