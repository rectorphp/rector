<?php

declare (strict_types=1);
namespace RectorPrefix20211226\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix20211226\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20211226\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(\RectorPrefix20211226\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20211226\Symfony\Component\Config\Loader\LoaderInterface;
}
