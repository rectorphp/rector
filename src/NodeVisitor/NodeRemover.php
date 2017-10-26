<?php declare(strict_types=1);

namespace Rector\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;
use Rector\NodeVisitor\Collector\NodeCollector;

final class NodeRemover extends NodeVisitorAbstract
{
    /**
     * @var NodeCollector
     */
    private $nodeCollector;

    public function __construct(NodeCollector $nodeCollector)
    {
        $this->nodeCollector = $nodeCollector;
    }

    /**
     * @return int|null|Node
     */
    public function enterNode(Node $node)
    {
        $nodesToRemove = $this->nodeCollector->getNodesToRemove();
        if (! count($nodesToRemove)) {
            return NodeTraverser::STOP_TRAVERSAL;
        }

        if (! in_array($node, $nodesToRemove, true)) {
            return null;
        }

        $node->getAttribute(Attribute::PARENT_NODE)
            ->setAttribute(Attribute::ORIGINAL_NODE, null);

        return new Nop;
    }
}
