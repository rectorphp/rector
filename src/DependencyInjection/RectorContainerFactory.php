<?php

declare (strict_types=1);
namespace Rector\DependencyInjection;

use Rector\Autoloading\BootstrapFilesIncluder;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Config\RectorConfig;
use Rector\ValueObject\Bootstrap\BootstrapConfigs;
final class RectorContainerFactory
{
    public function createFromBootstrapConfigs(BootstrapConfigs $bootstrapConfigs): RectorConfig
    {
        $rectorConfig = $this->createFromConfigs($bootstrapConfigs->getConfigFiles());
        $mainConfigFile = $bootstrapConfigs->getMainConfigFile();
        if ($mainConfigFile !== null) {
            /** @var ChangedFilesDetector $changedFilesDetector */
            $changedFilesDetector = $rectorConfig->make(ChangedFilesDetector::class);
            $changedFilesDetector->setFirstResolvedConfigFileInfo($mainConfigFile);
        }
        /** @var BootstrapFilesIncluder $bootstrapFilesIncluder */
        $bootstrapFilesIncluder = $rectorConfig->get(BootstrapFilesIncluder::class);
        $bootstrapFilesIncluder->includeBootstrapFiles();
        return $rectorConfig;
    }
    /**
     * @param string[] $configFiles
     */
    private function createFromConfigs(array $configFiles): RectorConfig
    {
        $lazyContainerFactory = new \Rector\DependencyInjection\LazyContainerFactory();
        $rectorConfig = $lazyContainerFactory->create();
        foreach ($configFiles as $configFile) {
            $rectorConfig->import($configFile);
        }
        $rectorConfig->boot();
        return $rectorConfig;
    }
}
