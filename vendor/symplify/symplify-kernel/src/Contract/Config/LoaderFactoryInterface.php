<?php

declare (strict_types=1);
namespace RectorPrefix20220524\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix20220524\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20220524\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(\RectorPrefix20220524\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : \RectorPrefix20220524\Symfony\Component\Config\Loader\LoaderInterface;
}
