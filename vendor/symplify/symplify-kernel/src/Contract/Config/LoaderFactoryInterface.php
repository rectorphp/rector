<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix202208\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix202208\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : LoaderInterface;
}
