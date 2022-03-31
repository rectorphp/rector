<?php

declare (strict_types=1);
namespace RectorPrefix20220331\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix20220331\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20220331\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(\RectorPrefix20220331\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20220331\Symfony\Component\Config\Loader\LoaderInterface;
}
