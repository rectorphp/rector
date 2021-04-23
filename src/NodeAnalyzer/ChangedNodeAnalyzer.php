<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Encapsed;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ChangedNodeAnalyzer
{
    /**
     * @var NodeComparator
     */
    private $nodeComparator;

    public function __construct(NodeComparator $nodeComparator)
    {
        $this->nodeComparator = $nodeComparator;
    }

    public function hasNodeChanged(Node $originalNode, Node $node): bool
    {
        if ($this->isNameIdentical($node, $originalNode)) {
            return false;
        }

        // @see https://github.com/rectorphp/rector/issues/6169 - special check, as php-parser skips brackets
        if ($node instanceof Encapsed) {
            foreach ($node->parts as $encapsedPart) {
                $originalEncapsedPart = $encapsedPart->getAttribute(AttributeKey::ORIGINAL_NODE);
                if ($originalEncapsedPart === null) {
                    return true;
                }
            }
        }

        return ! $this->nodeComparator->areNodesEqual($originalNode, $node);
    }

    private function isNameIdentical(Node $node, Node $originalNode): bool
    {
        if (! $originalNode instanceof Name) {
            return false;
        }

        // names are the same
        $originalName = $originalNode->getAttribute(AttributeKey::ORIGINAL_NAME);
        return $this->nodeComparator->areNodesEqual($originalName, $node);
    }
}
