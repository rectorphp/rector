<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SimplePhpDocParser\Contract;

use PHPStan\PhpDocParser\Ast\Node;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitor.php
 */
interface PhpDocNodeVisitorInterface
{
    public function beforeTraverse(Node $node) : void;
    public function enterNode(Node $node) : ?Node;
    public function leaveNode(Node $node) : void;
    public function afterTraverse(Node $node) : void;
}
