<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class PhpDocInfoRemovingNodeVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node): Node
    {
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, null);
        return $node;
    }
}
