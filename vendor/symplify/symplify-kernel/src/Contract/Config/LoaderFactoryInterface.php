<?php

declare (strict_types=1);
namespace RectorPrefix20211211\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix20211211\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20211211\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(\RectorPrefix20211211\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20211211\Symfony\Component\Config\Loader\LoaderInterface;
}
