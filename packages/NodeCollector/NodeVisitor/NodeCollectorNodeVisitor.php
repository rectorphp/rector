<?php

declare (strict_types=1);
namespace Rector\NodeCollector\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
final class NodeCollectorNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    /**
     * @var \Rector\NodeCollector\NodeCollector\ParsedNodeCollector
     */
    private $parsedNodeCollector;
    public function __construct(\Rector\NodeCollector\NodeCollector\ParsedNodeCollector $parsedNodeCollector)
    {
        $this->parsedNodeCollector = $parsedNodeCollector;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function enterNode($node)
    {
        $this->parsedNodeCollector->collect($node);
        return null;
    }
}
