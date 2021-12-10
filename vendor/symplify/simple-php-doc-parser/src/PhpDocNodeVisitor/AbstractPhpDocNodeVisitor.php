<?php

declare (strict_types=1);
namespace RectorPrefix20211210\Symplify\SimplePhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20211210\Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitorAbstract.php
 */
abstract class AbstractPhpDocNodeVisitor implements \RectorPrefix20211210\Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface
{
    /**
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function beforeTraverse($node) : void
    {
    }
    /**
     * @return int|Node|null
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function enterNode($node)
    {
        return null;
    }
    /**
     * @return null|int|\PhpParser\Node|Node[] Replacement node (or special return)
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function leaveNode($node)
    {
        return null;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function afterTraverse($node) : void
    {
    }
}
