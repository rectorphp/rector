<?php

declare (strict_types=1);
namespace RectorPrefix20210701\Symplify\SimplePhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20210701\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey;
/**
 * Mirrors
 * https://github.com/nikic/PHP-Parser/blob/d520bc9e1d6203c35a1ba20675b79a051c821a9e/lib/PhpParser/NodeVisitor/CloningVisitor.php
 */
final class CloningPhpDocNodeVisitor extends \RectorPrefix20210701\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor
{
    /**
     * @return int|Node|null
     */
    public function enterNode(\PHPStan\PhpDocParser\Ast\Node $origNode)
    {
        $node = clone $origNode;
        $node->setAttribute(\RectorPrefix20210701\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey::ORIG_NODE, $origNode);
        return $node;
    }
}
