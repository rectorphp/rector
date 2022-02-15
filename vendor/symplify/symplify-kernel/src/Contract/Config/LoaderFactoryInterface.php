<?php

declare (strict_types=1);
namespace RectorPrefix20220215\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix20220215\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20220215\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(\RectorPrefix20220215\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20220215\Symfony\Component\Config\Loader\LoaderInterface;
}
