<?php declare(strict_types=1);

namespace Rector\DependencyInjection;

use Psr\Container\ContainerInterface;
use function Safe\putenv;

final class ContainerFactory
{
    public function create(): ContainerInterface
    {
        $appKernel = new RectorKernel();
        $appKernel->boot();
        // this is require to keep CLI verbosity independent on AppKernel dev/prod mode
        putenv('SHELL_VERBOSITY=0');

        return $appKernel->getContainer();
    }

    /**
     * @param string[] $configFiles
     */
    public function createWithConfigFiles(array $configFiles): ContainerInterface
    {
        $appKernel = new RectorKernel($configFiles);
        $appKernel->boot();
        // this is require to keep CLI verbosity independent on AppKernel dev/prod mode
        putenv('SHELL_VERBOSITY=0');

        return $appKernel->getContainer();
    }
}
