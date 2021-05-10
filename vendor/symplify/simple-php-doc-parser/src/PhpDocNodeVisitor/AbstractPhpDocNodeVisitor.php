<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitorAbstract.php
 */
abstract class AbstractPhpDocNodeVisitor implements PhpDocNodeVisitorInterface
{
    public function beforeTraverse(Node $node) : void
    {
    }
    public function enterNode(Node $node) : ?Node
    {
        return null;
    }
    public function leaveNode(Node $node) : void
    {
    }
    public function afterTraverse(Node $node) : void
    {
    }
}
