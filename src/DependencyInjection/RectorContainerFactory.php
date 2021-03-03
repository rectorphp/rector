<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection;

use Psr\Container\ContainerInterface;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Configuration\Configuration;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\Stubs\PHPStanStubLoader;
use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symplify\PackageBuilder\Console\Input\StaticInputDetector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorContainerFactory
{
    /**
     * @param SmartFileInfo[] $configFileInfos
     * @api
     */
    public function createFromConfigs(array $configFileInfos): ContainerInterface
    {
        // to override the configs without clearing cache
        $isDebug = StaticInputDetector::isDebug();

        $environment = $this->createEnvironment($configFileInfos);

        // mt_rand is needed to invalidate container cache in case of class changes to be registered as services
        if (! StaticPHPUnitEnvironment::isPHPUnitRun()) {
            $environment .= mt_rand(0, 10000);
        }

        $rectorKernel = new RectorKernel($environment, $isDebug);
        if ($configFileInfos !== []) {
            $configFilePaths = $this->unpackRealPathsFromFileInfos($configFileInfos);
            $rectorKernel->setConfigs($configFilePaths);
        }

        $phpStanStubLoader = new PHPStanStubLoader();
        $phpStanStubLoader->loadStubs();

        $rectorKernel->boot();

        return $rectorKernel->getContainer();
    }

    public function createFromBootstrapConfigs(BootstrapConfigs $bootstrapConfigs): ContainerInterface
    {
        $container = $this->createFromConfigs($bootstrapConfigs->getConfigFileInfos());

        $mainConfigFileInfo = $bootstrapConfigs->getMainConfigFileInfo();
        if ($mainConfigFileInfo !== null) {
            /** @var ChangedFilesDetector $changedFilesDetector */
            $changedFilesDetector = $container->get(ChangedFilesDetector::class);
            $changedFilesDetector->setFirstResolvedConfigFileInfo($mainConfigFileInfo);
        }

        /** @var Configuration $configuration */
        $configuration = $container->get(Configuration::class);
        $configuration->setBootstrapConfigs($bootstrapConfigs);

        return $container;
    }

    /**
     * @param SmartFileInfo[] $configFileInfos
     * @return string[]
     */
    private function unpackRealPathsFromFileInfos(array $configFileInfos): array
    {
        $configFilePaths = [];
        foreach ($configFileInfos as $configFileInfo) {
            // getRealPath() cannot be used, as it breaks in phar
            $configFilePaths[] = $configFileInfo->getRealPath() ?: $configFileInfo->getPathname();
        }

        return $configFilePaths;
    }

    /**
     * @see https://symfony.com/doc/current/components/dependency_injection/compilation.html#dumping-the-configuration-for-performance
     * @param SmartFileInfo[] $configFileInfos
     */
    private function createEnvironment(array $configFileInfos): string
    {
        $configHashes = [];
        foreach ($configFileInfos as $configFileInfo) {
            $configHashes[] = md5_file($configFileInfo->getRealPath());
        }

        $configHashString = implode('', $configHashes);
        return sha1($configHashString);
    }
}
