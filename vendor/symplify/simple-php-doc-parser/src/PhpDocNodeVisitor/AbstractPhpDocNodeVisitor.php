<?php

declare (strict_types=1);
namespace RectorPrefix20210705\Symplify\SimplePhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20210705\Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitorAbstract.php
 */
abstract class AbstractPhpDocNodeVisitor implements \RectorPrefix20210705\Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface
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
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function leaveNode($node) : void
    {
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function afterTraverse($node) : void
    {
    }
}
