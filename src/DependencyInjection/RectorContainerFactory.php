<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection;

use RectorPrefix202308\Psr\Container\ContainerInterface;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Kernel\RectorKernel;
use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
final class RectorContainerFactory
{
    public function createFromBootstrapConfigs(BootstrapConfigs $bootstrapConfigs) : ContainerInterface
    {
        $container = $this->createFromConfigs($bootstrapConfigs->getConfigFiles());
        $mainConfigFile = $bootstrapConfigs->getMainConfigFile();
        if ($mainConfigFile !== null) {
            /** @var ChangedFilesDetector $changedFilesDetector */
            $changedFilesDetector = $container->get(ChangedFilesDetector::class);
            $changedFilesDetector->setFirstResolvedConfigFileInfo($mainConfigFile);
        }
        /** @var BootstrapFilesIncluder $bootstrapFilesIncluder */
        $bootstrapFilesIncluder = $container->get(BootstrapFilesIncluder::class);
        $bootstrapFilesIncluder->includeBootstrapFiles();
        return $container;
    }
    /**
     * @param string[] $configFiles
     * @api
     */
    private function createFromConfigs(array $configFiles) : ContainerInterface
    {
        $rectorKernel = new RectorKernel();
        return $rectorKernel->createFromConfigs($configFiles);
    }
}
