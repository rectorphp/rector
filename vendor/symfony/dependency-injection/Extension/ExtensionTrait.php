<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220607\Symfony\Component\DependencyInjection\Extension;

use RectorPrefix20220607\Symfony\Component\Config\Builder\ConfigBuilderGenerator;
use RectorPrefix20220607\Symfony\Component\Config\FileLocator;
use RectorPrefix20220607\Symfony\Component\Config\Loader\DelegatingLoader;
use RectorPrefix20220607\Symfony\Component\Config\Loader\LoaderResolver;
use RectorPrefix20220607\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
trait ExtensionTrait
{
    private function executeConfiguratorCallback(\RectorPrefix20220607\Symfony\Component\DependencyInjection\ContainerBuilder $container, \Closure $callback, \RectorPrefix20220607\Symfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface $subject) : void
    {
        $env = $container->getParameter('kernel.environment');
        $loader = $this->createContainerLoader($container, $env);
        $file = (new \ReflectionObject($subject))->getFileName();
        $bundleLoader = $loader->getResolver()->resolve($file);
        if (!$bundleLoader instanceof \RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\PhpFileLoader) {
            throw new \LogicException('Unable to create the ContainerConfigurator.');
        }
        $bundleLoader->setCurrentDir(\dirname($file));
        $instanceof =& \Closure::bind(function &() {
            return $this->instanceof;
        }, $bundleLoader, $bundleLoader)();
        try {
            $callback(new \RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator($container, $bundleLoader, $instanceof, $file, $file, $env));
        } finally {
            $instanceof = [];
            $bundleLoader->registerAliasesForSinglyImplementedInterfaces();
        }
    }
    private function createContainerLoader(\RectorPrefix20220607\Symfony\Component\DependencyInjection\ContainerBuilder $container, string $env) : \RectorPrefix20220607\Symfony\Component\Config\Loader\DelegatingLoader
    {
        $buildDir = $container->getParameter('kernel.build_dir');
        $locator = new \RectorPrefix20220607\Symfony\Component\Config\FileLocator();
        $resolver = new \RectorPrefix20220607\Symfony\Component\Config\Loader\LoaderResolver([new \RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\XmlFileLoader($container, $locator, $env), new \RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, $locator, $env), new \RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\IniFileLoader($container, $locator, $env), new \RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($container, $locator, $env, new \RectorPrefix20220607\Symfony\Component\Config\Builder\ConfigBuilderGenerator($buildDir)), new \RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\GlobFileLoader($container, $locator, $env), new \RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\DirectoryLoader($container, $locator, $env), new \RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\ClosureLoader($container, $env)]);
        return new \RectorPrefix20220607\Symfony\Component\Config\Loader\DelegatingLoader($resolver);
    }
}
