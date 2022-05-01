<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220501\Symplify\Astral\PhpDocParser\ValueObject\PhpDocAttributeKey;
/**
 * @api
 *
 * Mirrors
 * https://github.com/nikic/PHP-Parser/blob/d520bc9e1d6203c35a1ba20675b79a051c821a9e/lib/PhpParser/NodeVisitor/CloningVisitor.php
 */
final class CloningPhpDocNodeVisitor extends \RectorPrefix20220501\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor
{
    public function enterNode(\PHPStan\PhpDocParser\Ast\Node $node) : \PHPStan\PhpDocParser\Ast\Node
    {
        $clonedNode = clone $node;
        $clonedNode->setAttribute(\RectorPrefix20220501\Symplify\Astral\PhpDocParser\ValueObject\PhpDocAttributeKey::ORIG_NODE, $node);
        return $clonedNode;
    }
}
