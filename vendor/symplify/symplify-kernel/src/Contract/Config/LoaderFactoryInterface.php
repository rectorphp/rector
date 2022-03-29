<?php

declare (strict_types=1);
namespace RectorPrefix20220329\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix20220329\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20220329\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(\RectorPrefix20220329\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20220329\Symfony\Component\Config\Loader\LoaderInterface;
}
