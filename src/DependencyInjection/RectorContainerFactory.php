<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection;

use RectorPrefix20211101\Psr\Container\ContainerInterface;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Kernel\RectorKernel;
use Rector\Core\Stubs\PHPStanStubLoader;
use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
final class RectorContainerFactory
{
    /**
     * @param string[] $configFiles
     * @api
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20211101\Psr\Container\ContainerInterface
    {
        // to override the configs without clearing cache
        //        $isDebug = StaticInputDetector::isDebug();
        $phpStanStubLoader = new \Rector\Core\Stubs\PHPStanStubLoader();
        $phpStanStubLoader->loadStubs();
        $rectorKernel = new \Rector\Core\Kernel\RectorKernel();
        return $rectorKernel->createFromConfigs($configFiles);
    }
    public function createFromBootstrapConfigs(\Rector\Core\ValueObject\Bootstrap\BootstrapConfigs $bootstrapConfigs) : \RectorPrefix20211101\Psr\Container\ContainerInterface
    {
        $container = $this->createFromConfigs($bootstrapConfigs->getConfigFiles());
        $mainConfigFile = $bootstrapConfigs->getMainConfigFile();
        if ($mainConfigFile !== null) {
            /** @var ChangedFilesDetector $changedFilesDetector */
            $changedFilesDetector = $container->get(\Rector\Caching\Detector\ChangedFilesDetector::class);
            $changedFilesDetector->setFirstResolvedConfigFileInfo($mainConfigFile);
        }
        return $container;
    }
    /**
     * @see https://symfony.com/doc/current/components/dependency_injection/compilation.html#dumping-the-configuration-for-performance
     * @param string[] $configFiles
     */
}
