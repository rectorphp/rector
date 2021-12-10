<?php

declare (strict_types=1);
namespace Rector\Comments\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class CommentRemovingNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    public function enterNode(\PhpParser\Node $node) : \PhpParser\Node
    {
        // the node must be cloned, so original node is not touched in final print
        $clonedNode = clone $node;
        $clonedNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, []);
        $clonedNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, null);
        return $clonedNode;
    }
}
