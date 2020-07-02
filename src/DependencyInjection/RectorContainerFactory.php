<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection;

use Psr\Container\ContainerInterface;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Console\Input\InputDetector;

final class RectorContainerFactory
{
    /**
     * @api
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
}
