<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\Stubs\PHPStanStubLoader;
use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use Rector\Core\ValueObject\Configuration;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20211020\Symplify\PackageBuilder\Console\Input\StaticInputDetector;
use Symplify\SmartFileSystem\SmartFileInfo;
final class RectorContainerFactory
{
    /**
     * @param SmartFileInfo[] $configFileInfos
     * @api
     */
    public function createFromConfigs(array $configFileInfos) : \RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerInterface
    {
        // to override the configs without clearing cache
        $isDebug = \RectorPrefix20211020\Symplify\PackageBuilder\Console\Input\StaticInputDetector::isDebug();
        $environment = $this->createEnvironment($configFileInfos);
        // mt_rand is needed to invalidate container cache in case of class changes to be registered as services
        $isPHPUnitRun = \Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun();
        if (!$isPHPUnitRun) {
            $environment .= \random_int(0, 10000);
        }
        $phpStanStubLoader = new \Rector\Core\Stubs\PHPStanStubLoader();
        $phpStanStubLoader->loadStubs();
        $rectorKernel = new \Rector\Core\HttpKernel\RectorKernel($environment, $isDebug, $configFileInfos);
        $rectorKernel->boot();
        return $rectorKernel->getContainer();
    }
    public function createFromBootstrapConfigs(\Rector\Core\ValueObject\Bootstrap\BootstrapConfigs $bootstrapConfigs) : \RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerInterface
    {
        $container = $this->createFromConfigs($bootstrapConfigs->getConfigFileInfos());
        $mainConfigFileInfo = $bootstrapConfigs->getMainConfigFileInfo();
        if ($mainConfigFileInfo !== null) {
            /** @var ChangedFilesDetector $changedFilesDetector */
            $changedFilesDetector = $container->get(\Rector\Caching\Detector\ChangedFilesDetector::class);
            $changedFilesDetector->setFirstResolvedConfigFileInfo($mainConfigFileInfo);
        }
        return $container;
    }
    /**
     * @see https://symfony.com/doc/current/components/dependency_injection/compilation.html#dumping-the-configuration-for-performance
     * @param SmartFileInfo[] $configFileInfos
     */
    private function createEnvironment(array $configFileInfos) : string
    {
        $configHashes = [];
        foreach ($configFileInfos as $configFileInfo) {
            $configHashes[] = \md5_file($configFileInfo->getRealPath());
        }
        $configHashString = \implode('', $configHashes);
        return \sha1($configHashString);
    }
}
