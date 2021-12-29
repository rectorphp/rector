<?php

declare (strict_types=1);
namespace RectorPrefix20211229\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix20211229\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20211229\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(\RectorPrefix20211229\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20211229\Symfony\Component\Config\Loader\LoaderInterface;
}
