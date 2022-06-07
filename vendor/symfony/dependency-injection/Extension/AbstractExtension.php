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

use RectorPrefix20220607\Symfony\Component\Config\Definition\Configuration;
use RectorPrefix20220607\Symfony\Component\Config\Definition\ConfigurationInterface;
use RectorPrefix20220607\Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use RectorPrefix20220607\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
/**
 * An Extension that provides configuration hooks.
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
abstract class AbstractExtension extends \RectorPrefix20220607\Symfony\Component\DependencyInjection\Extension\Extension implements \RectorPrefix20220607\Symfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface, \RectorPrefix20220607\Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface
{
    use ExtensionTrait;
    public function configure(\RectorPrefix20220607\Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator $definition) : void
    {
    }
    public function prependExtension(\RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $container, \RectorPrefix20220607\Symfony\Component\DependencyInjection\ContainerBuilder $builder) : void
    {
    }
    public function loadExtension(array $config, \RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $container, \RectorPrefix20220607\Symfony\Component\DependencyInjection\ContainerBuilder $builder) : void
    {
    }
    public function getConfiguration(array $config, \RectorPrefix20220607\Symfony\Component\DependencyInjection\ContainerBuilder $container) : ?\RectorPrefix20220607\Symfony\Component\Config\Definition\ConfigurationInterface
    {
        return new \RectorPrefix20220607\Symfony\Component\Config\Definition\Configuration($this, $container, $this->getAlias());
    }
    public final function prepend(\RectorPrefix20220607\Symfony\Component\DependencyInjection\ContainerBuilder $container) : void
    {
        $callback = function (\RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $configurator) use($container) {
            $this->prependExtension($configurator, $container);
        };
        $this->executeConfiguratorCallback($container, $callback, $this);
    }
    public final function load(array $configs, \RectorPrefix20220607\Symfony\Component\DependencyInjection\ContainerBuilder $container) : void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $callback = function (\RectorPrefix20220607\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $configurator) use($config, $container) {
            $this->loadExtension($config, $configurator, $container);
        };
        $this->executeConfiguratorCallback($container, $callback, $this);
    }
}
