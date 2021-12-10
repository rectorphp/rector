<?php

declare (strict_types=1);
namespace RectorPrefix20211210\Symplify\SimplePhpDocParser\Contract;

use PHPStan\PhpDocParser\Ast\Node;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitor.php
 */
interface PhpDocNodeVisitorInterface
{
    /**
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function beforeTraverse($node) : void;
    /**
     * @return int|Node|null
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function enterNode($node);
    /**
     * @return null|int|\PhpParser\Node|Node[] Replacement node (or special return)
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function leaveNode($node);
    /**
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function afterTraverse($node) : void;
}
