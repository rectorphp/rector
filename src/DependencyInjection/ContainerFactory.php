<?php declare(strict_types=1);

namespace Rector\DependencyInjection;

use Psr\Container\ContainerInterface;

final class ContainerFactory
{
    public function create(): ContainerInterface
    {
        $appKernel = new AppKernel();
        $appKernel->boot();
        // this is require to keep CLI verbosity independent on AppKernel dev/prod mode
        putenv('SHELL_VERBOSITY=0');

        return $appKernel->getContainer();
    }

    public function createWithConfig(string $config): ContainerInterface
    {
        $appKernel = new AppKernel($config);
        $appKernel->boot();
        // this is require to keep CLI verbosity independent on AppKernel dev/prod mode
        putenv('SHELL_VERBOSITY=0');

        return $appKernel->getContainer();
    }
}
