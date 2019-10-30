<?php

declare(strict_types=1);

namespace Rector\DependencyInjection;

use Psr\Container\ContainerInterface;
use Rector\Bootstrap\SetOptionResolver;
use Rector\Exception\ShouldNotHappenException;
use Rector\HttpKernel\RectorKernel;
use Rector\Set\Set;
use Symplify\PackageBuilder\Configuration\ConfigFileFinder;
use Symplify\PackageBuilder\Console\Input\InputDetector;

final class RectorContainerFactory
{
    /**
     * @var SetOptionResolver
     */
    private $setOptionResolver;

    public function __construct()
    {
        $this->setOptionResolver = new SetOptionResolver();
    }

    public function createFromSet(string $set): ContainerInterface
    {
        $configFiles = $this->resolveConfigs($set);

        return $this->createFromConfigs($configFiles);
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
        if ($configFiles) {
            $rectorKernel->setConfigs($configFiles);
        }

        $rectorKernel->boot();

        return $rectorKernel->getContainer();
    }

    /**
     * @return string[]
     */
    private function resolveConfigs(string $set): array
    {
        $config = $this->setOptionResolver->detectFromNameAndDirectory($set, Set::SET_DIRECTORY);
        if ($config === null) {
            throw new ShouldNotHappenException(sprintf('Config file for "%s" set was not found', $set));
        }

        // copied mostly from https://github.com/rectorphp/rector/blob/master/bin/container.php
        $configFiles = [];
        $configFiles[] = $config;
        // local config has priority
        $configFiles[] = ConfigFileFinder::provide('rector', ['rector.yml', 'rector.yaml']);

        // remove empty values
        return array_filter($configFiles);
    }
}
