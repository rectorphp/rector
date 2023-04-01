<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202304\Symfony\Component\Config\Definition\Configurator;

use RectorPrefix202304\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use RectorPrefix202304\Symfony\Component\Config\Definition\Builder\NodeDefinition;
use RectorPrefix202304\Symfony\Component\Config\Definition\Builder\TreeBuilder;
use RectorPrefix202304\Symfony\Component\Config\Definition\Loader\DefinitionFileLoader;
/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
class DefinitionConfigurator
{
    /**
     * @var \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    private $treeBuilder;
    /**
     * @var \Symfony\Component\Config\Definition\Loader\DefinitionFileLoader
     */
    private $loader;
    /**
     * @var string
     */
    private $path;
    /**
     * @var string
     */
    private $file;
    public function __construct(TreeBuilder $treeBuilder, DefinitionFileLoader $loader, string $path, string $file)
    {
        $this->treeBuilder = $treeBuilder;
        $this->loader = $loader;
        $this->path = $path;
        $this->file = $file;
    }
    public function import(string $resource, string $type = null, bool $ignoreErrors = \false) : void
    {
        $this->loader->setCurrentDir(\dirname($this->path));
        $this->loader->import($resource, $type, $ignoreErrors, $this->file);
    }
    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition|\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    public function rootNode()
    {
        return $this->treeBuilder->getRootNode();
    }
    public function setPathSeparator(string $separator) : void
    {
        $this->treeBuilder->setPathSeparator($separator);
    }
}
