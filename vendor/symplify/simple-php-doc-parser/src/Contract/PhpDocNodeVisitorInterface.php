<?php

declare (strict_types=1);
namespace RectorPrefix20210514\Symplify\SimplePhpDocParser\Contract;

use PHPStan\PhpDocParser\Ast\Node;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitor.php
 */
interface PhpDocNodeVisitorInterface
{
    public function beforeTraverse(\PHPStan\PhpDocParser\Ast\Node $node) : void;
    public function enterNode(\PHPStan\PhpDocParser\Ast\Node $node) : ?\PHPStan\PhpDocParser\Ast\Node;
    public function leaveNode(\PHPStan\PhpDocParser\Ast\Node $node) : void;
    public function afterTraverse(\PHPStan\PhpDocParser\Ast\Node $node) : void;
}
