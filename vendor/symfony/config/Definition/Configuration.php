<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220531\Symfony\Component\Config\Definition;

use RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\TreeBuilder;
use RectorPrefix20220531\Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use RectorPrefix20220531\Symfony\Component\Config\Definition\Loader\DefinitionFileLoader;
use RectorPrefix20220531\Symfony\Component\Config\FileLocator;
use RectorPrefix20220531\Symfony\Component\DependencyInjection\ContainerBuilder;
/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 *
 * @final
 */
class Configuration implements \RectorPrefix20220531\Symfony\Component\Config\Definition\ConfigurationInterface
{
    /**
     * @var \Symfony\Component\Config\Definition\ConfigurableInterface
     */
    private $subject;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder|null
     */
    private $container;
    /**
     * @var string
     */
    private $alias;
    public function __construct(\RectorPrefix20220531\Symfony\Component\Config\Definition\ConfigurableInterface $subject, ?\RectorPrefix20220531\Symfony\Component\DependencyInjection\ContainerBuilder $container, string $alias)
    {
        $this->subject = $subject;
        $this->container = $container;
        $this->alias = $alias;
    }
    public function getConfigTreeBuilder() : \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\TreeBuilder
    {
        $treeBuilder = new \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\TreeBuilder($this->alias);
        $file = (new \ReflectionObject($this->subject))->getFileName();
        $loader = new \RectorPrefix20220531\Symfony\Component\Config\Definition\Loader\DefinitionFileLoader($treeBuilder, new \RectorPrefix20220531\Symfony\Component\Config\FileLocator(\dirname($file)), $this->container);
        $configurator = new \RectorPrefix20220531\Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator($treeBuilder, $loader, $file, $file);
        $this->subject->configure($configurator);
        return $treeBuilder;
    }
}
