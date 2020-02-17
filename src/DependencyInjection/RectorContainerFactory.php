<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection;

use Psr\Container\ContainerInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\Set\Set;
use Symplify\PackageBuilder\Console\Input\InputDetector;
use Symplify\SetConfigResolver\SetResolver;

final class RectorContainerFactory
{
    /**
     * @var string[]
     * local config has priority
     */
    private const LOCAL_CONFIGS = ['rector.yml', 'rector.yaml'];

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

    private function resolveConfigFromSet(string $set): string
    {
        $config = $this->setResolver->detectFromNameAndDirectory($set, Set::SET_DIRECTORY);
        if ($config === null) {
            throw new ShouldNotHappenException(sprintf('Config file for "%s" set was not found', $set));
        }

        return $config;
    }

    /**
     * @return string[]
     */
    private function resolveLocalConfigs(): array
    {
        $configs = [];
        foreach (self::LOCAL_CONFIGS as $localConfig) {
            $configRealPath = getcwd() . DIRECTORY_SEPARATOR . $localConfig;
            if (! file_exists($configRealPath)) {
                continue;
            }

            $configs[] = $configRealPath;
        }

        return $configs;
    }
}
