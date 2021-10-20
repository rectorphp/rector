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

/**
 * This class provides a fluent interface for building a node.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class NodeBuilder implements \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\NodeParentInterface
{
    protected $parent;
    protected $nodeMapping;
    public function __construct()
    {
        $this->nodeMapping = ['variable' => \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\VariableNodeDefinition::class, 'scalar' => \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition::class, 'boolean' => \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition::class, 'integer' => \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition::class, 'float' => \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\FloatNodeDefinition::class, 'array' => \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition::class, 'enum' => \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\EnumNodeDefinition::class];
    }
    /**
     * Set the parent node.
     *
     * @return $this
     * @param \Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface|null $parent
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;
        return $this;
    }
    /**
     * Creates a child array node.
     *
     * @return ArrayNodeDefinition The child node
     * @param string $name
     */
    public function arrayNode($name)
    {
        return $this->node($name, 'array');
    }
    /**
     * Creates a child scalar node.
     *
     * @return ScalarNodeDefinition The child node
     * @param string $name
     */
    public function scalarNode($name)
    {
        return $this->node($name, 'scalar');
    }
    /**
     * Creates a child Boolean node.
     *
     * @return BooleanNodeDefinition The child node
     * @param string $name
     */
    public function booleanNode($name)
    {
        return $this->node($name, 'boolean');
    }
    /**
     * Creates a child integer node.
     *
     * @return IntegerNodeDefinition The child node
     * @param string $name
     */
    public function integerNode($name)
    {
        return $this->node($name, 'integer');
    }
    /**
     * Creates a child float node.
     *
     * @return FloatNodeDefinition The child node
     * @param string $name
     */
    public function floatNode($name)
    {
        return $this->node($name, 'float');
    }
    /**
     * Creates a child EnumNode.
     *
     * @return EnumNodeDefinition
     * @param string $name
     */
    public function enumNode($name)
    {
        return $this->node($name, 'enum');
    }
    /**
     * Creates a child variable node.
     *
     * @return VariableNodeDefinition The builder of the child node
     * @param string $name
     */
    public function variableNode($name)
    {
        return $this->node($name, 'variable');
    }
    /**
     * Returns the parent node.
     *
     * @return NodeDefinition&ParentNodeDefinitionInterface The parent node
     */
    public function end()
    {
        return $this->parent;
    }
    /**
     * Creates a child node.
     *
     * @return NodeDefinition The child node
     *
     * @throws \RuntimeException When the node type is not registered
     * @throws \RuntimeException When the node class is not found
     * @param string|null $name
     * @param string $type
     */
    public function node($name, $type)
    {
        $class = $this->getNodeClass($type);
        $node = new $class($name);
        $this->append($node);
        return $node;
    }
    /**
     * Appends a node definition.
     *
     * Usage:
     *
     *     $node = new ArrayNodeDefinition('name')
     *         ->children()
     *             ->scalarNode('foo')->end()
     *             ->scalarNode('baz')->end()
     *             ->append($this->getBarNodeDefinition())
     *         ->end()
     *     ;
     *
     * @return $this
     * @param \Symfony\Component\Config\Definition\Builder\NodeDefinition $node
     */
    public function append($node)
    {
        if ($node instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\Builder\BuilderAwareInterface) {
            $builder = clone $this;
            $builder->setParent(null);
            $node->setBuilder($builder);
        }
        if (null !== $this->parent) {
            $this->parent->append($node);
            // Make this builder the node parent to allow for a fluid interface
            $node->setParent($this);
        }
        return $this;
    }
    /**
     * Adds or overrides a node Type.
     *
     * @param string $type  The name of the type
     * @param string $class The fully qualified name the node definition class
     *
     * @return $this
     */
    public function setNodeClass($type, $class)
    {
        $this->nodeMapping[\strtolower($type)] = $class;
        return $this;
    }
    /**
     * Returns the class name of the node definition.
     *
     * @return string The node definition class name
     *
     * @throws \RuntimeException When the node type is not registered
     * @throws \RuntimeException When the node class is not found
     * @param string $type
     */
    protected function getNodeClass($type)
    {
        $type = \strtolower($type);
        if (!isset($this->nodeMapping[$type])) {
            throw new \RuntimeException(\sprintf('The node type "%s" is not registered.', $type));
        }
        $class = $this->nodeMapping[$type];
        if (!\class_exists($class)) {
            throw new \RuntimeException(\sprintf('The node class "%s" does not exist.', $class));
        }
        return $class;
    }
}
