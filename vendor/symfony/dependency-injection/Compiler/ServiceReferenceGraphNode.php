<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20211020\Symfony\Component\DependencyInjection\Alias;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition;
/**
 * Represents a node in your service graph.
 *
 * Value is typically a definition, or an alias.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ServiceReferenceGraphNode
{
    private $id;
    private $inEdges = [];
    private $outEdges = [];
    private $value;
    /**
     * @param string $id    The node identifier
     * @param mixed  $value The node value
     */
    public function __construct(string $id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }
    /**
     * @param \Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraphEdge $edge
     */
    public function addInEdge($edge)
    {
        $this->inEdges[] = $edge;
    }
    /**
     * @param \Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraphEdge $edge
     */
    public function addOutEdge($edge)
    {
        $this->outEdges[] = $edge;
    }
    /**
     * Checks if the value of this node is an Alias.
     *
     * @return bool True if the value is an Alias instance
     */
    public function isAlias()
    {
        return $this->value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\Alias;
    }
    /**
     * Checks if the value of this node is a Definition.
     *
     * @return bool True if the value is a Definition instance
     */
    public function isDefinition()
    {
        return $this->value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition;
    }
    /**
     * Returns the identifier.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Returns the in edges.
     *
     * @return ServiceReferenceGraphEdge[]
     */
    public function getInEdges()
    {
        return $this->inEdges;
    }
    /**
     * Returns the out edges.
     *
     * @return ServiceReferenceGraphEdge[]
     */
    public function getOutEdges()
    {
        return $this->outEdges;
    }
    /**
     * Returns the value of this Node.
     *
     * @return mixed The value
     */
    public function getValue()
    {
        return $this->value;
    }
    /**
     * Clears all edges.
     */
    public function clear()
    {
        $this->inEdges = $this->outEdges = [];
    }
}
