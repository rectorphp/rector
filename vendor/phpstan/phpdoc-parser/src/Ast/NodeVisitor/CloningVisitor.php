<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\NodeVisitor;

use PHPStan\PhpDocParser\Ast\AbstractNodeVisitor;
use PHPStan\PhpDocParser\Ast\Attribute;
use PHPStan\PhpDocParser\Ast\Node;
final class CloningVisitor extends AbstractNodeVisitor
{
    public function enterNode(Node $originalNode) : Node
    {
        $node = clone $originalNode;
        $node->setAttribute(Attribute::ORIGINAL_NODE, $originalNode);
        return $node;
    }
}
