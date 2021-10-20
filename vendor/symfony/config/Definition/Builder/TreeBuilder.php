<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Config\Definition\Builder;

use RectorPrefix20211020\Symfony\Component\Config\Definition\NodeInterface;
/**
 * This is the entry class for building a config tree.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class TreeBuilder implements \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\NodeParentInterface
{
    protected $tree;
    protected $root;
    public function __construct(string $name, string $type = 'array', \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\NodeBuilder $builder = null)
    {
        $builder = $builder ?? new \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $this->root = $builder->node($name, $type)->setParent($this);
    }
    /**
     * @return NodeDefinition|ArrayNodeDefinition The root node (as an ArrayNodeDefinition when the type is 'array')
     */
    public function getRootNode() : \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\NodeDefinition
    {
        return $this->root;
    }
    /**
     * Builds the tree.
     *
     * @return NodeInterface
     *
     * @throws \RuntimeException
     */
    public function buildTree()
    {
        if (null !== $this->tree) {
            return $this->tree;
        }
        return $this->tree = $this->root->getNode(\true);
    }
    /**
     * @param string $separator
     */
    public function setPathSeparator($separator)
    {
        // unset last built as changing path separator changes all nodes
        $this->tree = null;
        $this->root->setPathSeparator($separator);
    }
}
