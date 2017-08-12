<?php declare(strict_types=1);

namespace Rector\NodeVisitor\Traverse;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class NodeConnectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Node
     */
    private $prev;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->prev = null;
    }

    public function enterNode(Node $node): void
    {
        if ($this->prev) {
            $node->setAttribute('prev', $this->prev);
            $this->prev->setAttribute('next', $node);
        }
    }

    public function leaveNode(Node $node): void
    {
        $this->prev = $node;
    }
}
