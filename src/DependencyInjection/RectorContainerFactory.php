<?php

declare(strict_types=1);

namespace Rector\DependencyInjection;

use Psr\Container\ContainerInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\HttpKernel\RectorKernel;
use Rector\Set\Set;
use Symplify\PackageBuilder\Console\Input\InputDetector;
use Symplify\SetConfigResolver\SetResolver;

final class RectorContainerFactory
{
    /**
     * @var SetResolver
     */
    private $setResolver;

    public function __construct()
    {
        $this->setResolver = new SetResolver();
    }

    public function createFromSet(string $set): ContainerInterface
    {
        $configs = [];
        $configs[] = $this->resolveConfigFromSet($set);

        $localConfigs = $this->resolveLocalConfigs();
        if ($localConfigs !== []) {
            // local config has priority, so it's first here
            $configs = array_merge($localConfigs, $configs);
        }

        return $this->createFromConfigs($configs);
    }

    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles): ContainerInterface
    {
        // to override the configs without clearing cache
        $environment = 'prod' . random_int(1, 10000000);
        $isDebug = InputDetector::isDebug();

        $rectorKernel = new RectorKernel($environment, $isDebug);
        if ($configFiles !== []) {
            $rectorKernel->setConfigs($configFiles);
        }

        $rectorKernel->boot();

        return $rectorKernel->getContainer();
    }

    /**
     * @return string[]
     */
    private function resolveLocalConfigs(): array
    {
        $configs = [];

        // local config has priority
        $localConfigs = ['rector.yml', 'rector.yaml'];
        foreach ($localConfigs as $localConfig) {
            $configRealPath = getcwd() . DIRECTORY_SEPARATOR . $localConfig;
            if (! file_exists($configRealPath)) {
                continue;
            }

            $configs[] = $configRealPath;
        }

        return $configs;
    }

    private function resolveConfigFromSet(string $set): string
    {
        $config = $this->setResolver->detectFromNameAndDirectory($set, Set::SET_DIRECTORY);
        if ($config === null) {
            throw new ShouldNotHappenException(sprintf('Config file for "%s" set was not found', $set));
        }

        return $config;
    }
}
