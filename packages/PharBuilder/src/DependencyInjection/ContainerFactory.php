<?php declare(strict_types=1);

namespace Rector\PharBuilder\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContainerFactory
{
    public function create(): ContainerInterface
    {
        $appKernel = new PharBuilderKernel();
        $appKernel->boot();

        // this is require to keep CLI verbosity independent on AppKernel dev/prod mode
        // see: https://github.com/symfony/symfony/blob/c12c07865a6fe3a8e0edd8540ac3ab9a3bc75543/src/Symfony/Component/HttpKernel/Kernel.php#L115
        putenv('SHELL_VERBOSITY=0');

        return $appKernel->getContainer();
    }
}
