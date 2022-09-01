<?php

declare (strict_types=1);
namespace RectorPrefix202209\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix202209\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix202209\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : LoaderInterface;
}
