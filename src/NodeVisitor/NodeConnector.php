<?php declare(strict_types=1);

namespace Rector\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * See https://github.com/nikic/PHP-Parser/blob/master/doc/5_FAQ.markdown#how-can-the-nextprevious-sibling-of-a-node-be-obtained.
 */
final class NodeConnector extends NodeVisitorAbstract
{
    /**
     * @var Node
     */
    private $stack;

    /**
     * @var Node
     */
    private $prev;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->stack = [];
        $this->prev = null;
    }

    public function enterNode(Node $node): void
    {
        if (! empty($this->stack)) {
            $node->setAttribute('parent', $this->stack[count($this->stack) - 1]);
        }

        if ($this->prev && $this->prev->getAttribute('parent') === $node->getAttribute('parent')) {
            $node->setAttribute('prev', $this->prev);
            $this->prev->setAttribute('next', $node);
        }

        $this->stack[] = $node;
    }

    public function leaveNode(Node $node): void
    {
        $this->prev = $node;
        array_pop($this->stack);
    }
}
