<?php

namespace Rector\Symfony\Tests\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector\Fixture;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class SomeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator());
        $loader->load(__DIR__ . '/../Resources/config/controller.xml');
        $loader->load(__DIR__ . '/../Resources/config/events.xml');
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector\Fixture;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class SomeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new \Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, new FileLocator());
        $loader->load(__DIR__ . '/../Resources/config/controller.yaml');
        $loader->load(__DIR__ . '/../Resources/config/events.yaml');
    }
}

?>
