<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\Attribute;

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
     * @return Node[]|null
     */
    public function afterTraverse(array $nodes): ?array
    {
        $this->stack = [];
        $this->prev = null;

        return null;
    }

    /**
     * @return int|Node|null
     */
    public function enterNode(Node $node)
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

        return null;
    }

    /**
     * @return Node[]|int|Node|null
     */
    public function leaveNode(Node $node)
    {
        $this->prev = $node;
        array_pop($this->stack);

        return null;
    }
}
