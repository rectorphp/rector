<?php declare(strict_types=1);

namespace Rector\NodeVisitor\Traverse;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class ParentConnectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Node[]
     */
    private $stack = [];

    public function beforeTraverse(array $nodes): void
    {
        $this->stack = [];
    }

    public function enterNode(Node $node): void
    {
        if (! empty($this->stack)) {
            $node->setAttribute('parent', $this->stack[count($this->stack)-1]);
        }

        $this->stack[] = $node;
    }

    public function leaveNode(Node $node): void
    {
        array_pop($this->stack);
    }
}
