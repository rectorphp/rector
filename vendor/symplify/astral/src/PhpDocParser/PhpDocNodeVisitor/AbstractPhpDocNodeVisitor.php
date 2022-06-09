<?php

declare (strict_types=1);
namespace RectorPrefix20220609\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220609\Symplify\Astral\PhpDocParser\Contract\PhpDocNodeVisitorInterface;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitorAbstract.php
 */
abstract class AbstractPhpDocNodeVisitor implements PhpDocNodeVisitorInterface
{
    public function beforeTraverse(Node $node) : void
    {
    }
    /**
     * @return int|\PHPStan\PhpDocParser\Ast\Node|null
     */
    public function enterNode(Node $node)
    {
        return null;
    }
    /**
     * @return int|\PhpParser\Node|mixed[]|null Replacement node (or special return)
     */
    public function leaveNode(Node $node)
    {
        return null;
    }
    public function afterTraverse(Node $node) : void
    {
    }
}
