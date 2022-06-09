<?php

declare (strict_types=1);
namespace RectorPrefix20220609\Symplify\Astral\PhpDocParser\Contract;

use PHPStan\PhpDocParser\Ast\Node;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitor.php
 */
interface PhpDocNodeVisitorInterface
{
    public function beforeTraverse(Node $node) : void;
    /**
     * @return int|\PHPStan\PhpDocParser\Ast\Node|null
     */
    public function enterNode(Node $node);
    /**
     * @return int|\PhpParser\Node|mixed[]|null Replacement node (or special return)
     */
    public function leaveNode(Node $node);
    public function afterTraverse(Node $node) : void;
}
