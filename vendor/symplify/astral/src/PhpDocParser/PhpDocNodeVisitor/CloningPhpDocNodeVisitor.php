<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix202208\Symplify\Astral\PhpDocParser\ValueObject\PhpDocAttributeKey;
/**
 * @api
 *
 * Mirrors
 * https://github.com/nikic/PHP-Parser/blob/d520bc9e1d6203c35a1ba20675b79a051c821a9e/lib/PhpParser/NodeVisitor/CloningVisitor.php
 */
final class CloningPhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    public function enterNode(Node $node) : Node
    {
        $clonedNode = clone $node;
        $clonedNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, $node);
        return $clonedNode;
    }
}
