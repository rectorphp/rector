<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Symplify\SimplePhpDocParser\Contract;

use PHPStan\PhpDocParser\Ast\Node;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitor.php
 */
interface PhpDocNodeVisitorInterface
{
    public function beforeTraverse(\PHPStan\PhpDocParser\Ast\Node $node) : void;
    /**
     * @return int|Node|null
     */
    public function enterNode(\PHPStan\PhpDocParser\Ast\Node $node);
    /**
     * @return null|int|\PhpParser\Node|Node[] Replacement node (or special return)
     */
    public function leaveNode(\PHPStan\PhpDocParser\Ast\Node $node);
    public function afterTraverse(\PHPStan\PhpDocParser\Ast\Node $node) : void;
}
