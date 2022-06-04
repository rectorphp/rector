<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220604\Symfony\Component\DependencyInjection\Extension;

use RectorPrefix20220604\Symfony\Component\Config\Definition\ConfigurableInterface;
use RectorPrefix20220604\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220604\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
interface ConfigurableExtensionInterface extends \RectorPrefix20220604\Symfony\Component\Config\Definition\ConfigurableInterface
{
    /**
     * Allows an extension to prepend the extension configurations.
     */
    public function prependExtension(\RectorPrefix20220604\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $container, \RectorPrefix20220604\Symfony\Component\DependencyInjection\ContainerBuilder $builder) : void;
    /**
     * Loads a specific configuration.
     */
    public function loadExtension(array $config, \RectorPrefix20220604\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $container, \RectorPrefix20220604\Symfony\Component\DependencyInjection\ContainerBuilder $builder) : void;
}
