<?php

declare (strict_types=1);
namespace RectorPrefix20220219\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix20220219\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20220219\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(\RectorPrefix20220219\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20220219\Symfony\Component\Config\Loader\LoaderInterface;
}
