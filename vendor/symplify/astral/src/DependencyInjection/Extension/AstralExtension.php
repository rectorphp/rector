<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Symplify\Astral\DependencyInjection\Extension;

use RectorPrefix20210827\Symfony\Component\Config\FileLocator;
use RectorPrefix20210827\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210827\Symfony\Component\DependencyInjection\Extension\Extension;
use RectorPrefix20210827\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
final class AstralExtension extends \RectorPrefix20210827\Symfony\Component\DependencyInjection\Extension\Extension
{
    /**
     * @param string[] $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     */
    public function load($configs, $containerBuilder) : void
    {
        $phpFileLoader = new \RectorPrefix20210827\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($containerBuilder, new \RectorPrefix20210827\Symfony\Component\Config\FileLocator(__DIR__ . '/../../../config'));
        $phpFileLoader->load('config.php');
    }
}
