<?php

declare(strict_types=1);

namespace Rector\Compiler\DependencyInjection;

use Psr\Container\ContainerInterface;
use Rector\Compiler\HttpKernel\RectorCompilerKernel;

final class ContainerFactory
{
    public function create(): ContainerInterface
    {
        $environment = 'prod' . random_int(1, 10000000);
        $rectorCompilerKernel = new RectorCompilerKernel($environment, true);
        $rectorCompilerKernel->boot();

        return $rectorCompilerKernel->getContainer();
    }
}
