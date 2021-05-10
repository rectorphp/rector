<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SymplifyKernel\DependencyInjection\Extension;

use RectorPrefix20210510\Symfony\Component\Config\FileLocator;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Extension\Extension;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
final class SymplifyKernelExtension extends Extension
{
    /**
     * @param string[] $configs
     */
    public function load(array $configs, ContainerBuilder $containerBuilder) : void
    {
        $phpFileLoader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../../../config'));
        $phpFileLoader->load('common-config.php');
    }
}
