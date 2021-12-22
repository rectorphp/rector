<?php

declare (strict_types=1);
namespace RectorPrefix20211222\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix20211222\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20211222\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(\RectorPrefix20211222\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20211222\Symfony\Component\Config\Loader\LoaderInterface;
}
