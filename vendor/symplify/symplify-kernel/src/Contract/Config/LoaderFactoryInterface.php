<?php

declare (strict_types=1);
namespace RectorPrefix20220609\Symplify\SymplifyKernel\Contract\Config;

use RectorPrefix20220609\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20220609\Symfony\Component\DependencyInjection\ContainerBuilder;
interface LoaderFactoryInterface
{
    public function create(ContainerBuilder $containerBuilder, string $currentWorkingDirectory) : LoaderInterface;
}
