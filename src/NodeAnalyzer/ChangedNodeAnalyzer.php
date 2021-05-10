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
    public function __construct(
        private NodeComparator $nodeComparator
    ) {
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

        // php-parser has no idea about changed docblocks, so to report it correctly, we have to set up this attribute
        if ($node->hasAttribute(AttributeKey::HAS_PHP_DOC_INFO_JUST_CHANGED)) {
            $node->setAttribute(AttributeKey::HAS_PHP_DOC_INFO_JUST_CHANGED, null);
            return true;
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
