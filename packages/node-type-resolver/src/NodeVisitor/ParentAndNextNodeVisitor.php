<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/nikic/PHP-Parser/blob/master/doc/component/FAQ.markdown#how-can-the-nextprevious-sibling-of-a-node-be-obtained
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
    private $previousNode;

    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function afterTraverse(array $nodes): ?array
    {
        $this->stack = [];
        $this->previousNode = null;

        return null;
    }

    /**
     * @return int|Node|null
     */
    public function enterNode(Node $node)
    {
        if (! empty($this->stack)) {
            $node->setAttribute(AttributeKey::PARENT_NODE, $this->stack[count($this->stack) - 1]);
        }

        if ($this->previousNode &&
            $this->previousNode->getAttribute(AttributeKey::PARENT_NODE) === $node->getAttribute(
                AttributeKey::PARENT_NODE
            )
        ) {
            $node->setAttribute(AttributeKey::PREVIOUS_NODE, $this->previousNode);
            $this->previousNode->setAttribute(AttributeKey::NEXT_NODE, $node);
        }

        $this->stack[] = $node;

        return null;
    }

    /**
     * @return Node[]|int|Node|null
     */
    public function leaveNode(Node $node)
    {
        $this->previousNode = $node;
        array_pop($this->stack);

        return null;
    }
}
