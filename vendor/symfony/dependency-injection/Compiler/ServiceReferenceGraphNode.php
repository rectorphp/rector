<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202304\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix202304\Symfony\Component\DependencyInjection\Alias;
use RectorPrefix202304\Symfony\Component\DependencyInjection\Definition;
/**
 * Represents a node in your service graph.
 *
 * Value is typically a definition, or an alias.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ServiceReferenceGraphNode
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var mixed[]
     */
    private $inEdges = [];
    /**
     * @var mixed[]
     */
    private $outEdges = [];
    /**
     * @var mixed
     */
    private $value;
    /**
     * @param mixed $value
     */
    public function __construct(string $id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }
    public function addInEdge(ServiceReferenceGraphEdge $edge)
    {
        $this->inEdges[] = $edge;
    }
    public function addOutEdge(ServiceReferenceGraphEdge $edge)
    {
        $this->outEdges[] = $edge;
    }
    /**
     * Checks if the value of this node is an Alias.
     */
    public function isAlias() : bool
    {
        return $this->value instanceof Alias;
    }
    /**
     * Checks if the value of this node is a Definition.
     */
    public function isDefinition() : bool
    {
        return $this->value instanceof Definition;
    }
    /**
     * Returns the identifier.
     */
    public function getId() : string
    {
        return $this->id;
    }
    /**
     * Returns the in edges.
     *
     * @return ServiceReferenceGraphEdge[]
     */
    public function getInEdges() : array
    {
        return $this->inEdges;
    }
    /**
     * Returns the out edges.
     *
     * @return ServiceReferenceGraphEdge[]
     */
    public function getOutEdges() : array
    {
        return $this->outEdges;
    }
    /**
     * Returns the value of this Node.
     * @return mixed
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
