<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;

/**
 * See https://github.com/nikic/PHP-Parser/blob/master/doc/5_FAQ.markdown#how-can-the-nextprevious-sibling-of-a-node-be-obtained.
 */
final class ParentAndNextNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Node[]
     */
    private $stack = [];

    /**
     * @var Node|null
     */
    private $prev;

    /**
     * @param Node[] $nodes
     */
    public function afterTraverse(array $nodes): void
    {
        $this->stack = [];
        $this->prev = null;
    }

    public function enterNode(Node $node): void
    {
        if (! empty($this->stack)) {
            $node->setAttribute(Attribute::PARENT_NODE, $this->stack[count($this->stack) - 1]);
        }

        if ($this->prev &&
            $this->prev->getAttribute(Attribute::PARENT_NODE) === $node->getAttribute(Attribute::PARENT_NODE)
        ) {
            $node->setAttribute(Attribute::PREVIOUS_NODE, $this->prev);
            $this->prev->setAttribute(Attribute::NEXT_NODE, $node);
        }

        $this->stack[] = $node;
    }

    public function leaveNode(Node $node): void
    {
        $this->prev = $node;
        array_pop($this->stack);
    }
}
