<?php

declare (strict_types=1);
namespace RectorPrefix20210702\Symplify\SimplePhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20210702\Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitorAbstract.php
 */
abstract class AbstractPhpDocNodeVisitor implements \RectorPrefix20210702\Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface
{
    public function beforeTraverse(\PHPStan\PhpDocParser\Ast\Node $node) : void
    {
    }
    /**
     * @return int|Node|null
     */
    public function enterNode(\PHPStan\PhpDocParser\Ast\Node $node)
    {
        return null;
    }
    public function leaveNode(\PHPStan\PhpDocParser\Ast\Node $node) : void
    {
    }
    public function afterTraverse(\PHPStan\PhpDocParser\Ast\Node $node) : void
    {
    }
}
