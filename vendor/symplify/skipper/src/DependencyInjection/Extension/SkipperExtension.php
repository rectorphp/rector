<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\Skipper\DependencyInjection\Extension;

use RectorPrefix20210510\Symfony\Component\Config\FileLocator;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Extension\Extension;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
final class SkipperExtension extends Extension
{
    /**
     * @param string[] $configs
     */
    public function load(array $configs, ContainerBuilder $containerBuilder) : void
    {
        // needed for parameter shifting of sniff/fixer params
        $phpFileLoader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../../../config'));
        $phpFileLoader->load('config.php');
    }
}
