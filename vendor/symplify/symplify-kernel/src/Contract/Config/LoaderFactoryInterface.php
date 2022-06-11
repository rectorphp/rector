<?php

declare (strict_types=1);
namespace RectorPrefix202206\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix202206\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix202206\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : LoaderInterface;
}
