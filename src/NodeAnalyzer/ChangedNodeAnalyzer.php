<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Encapsed;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ChangedNodeAnalyzer
{
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeComparator = $nodeComparator;
    }
    public function hasNodeChanged(\PhpParser\Node $originalNode, \PhpParser\Node $node) : bool
    {
        if ($this->isNameIdentical($node, $originalNode)) {
            return \false;
        }
        // @see https://github.com/rectorphp/rector/issues/6169 - special check, as php-parser skips brackets
        if ($node instanceof \PhpParser\Node\Scalar\Encapsed) {
            foreach ($node->parts as $encapsedPart) {
                $originalEncapsedPart = $encapsedPart->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE);
                if ($originalEncapsedPart === null) {
                    return \true;
                }
            }
        }
        // php-parser has no idea about changed docblocks, so to report it correctly, we have to set up this attribute
        if ($node->hasAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::HAS_PHP_DOC_INFO_JUST_CHANGED)) {
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::HAS_PHP_DOC_INFO_JUST_CHANGED, null);
            return \true;
        }
        return !$this->nodeComparator->areNodesEqual($originalNode, $node);
    }
    private function isNameIdentical(\PhpParser\Node $node, \PhpParser\Node $originalNode) : bool
    {
        if (!$originalNode instanceof \PhpParser\Node\Name) {
            return \false;
        }
        // names are the same
        $originalName = $originalNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME);
        return $this->nodeComparator->areNodesEqual($originalName, $node);
    }
}
