<?php

declare (strict_types=1);
namespace RectorPrefix20211214\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix20211214\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20211214\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(\RectorPrefix20211214\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20211214\Symfony\Component\Config\Loader\LoaderInterface;
}
