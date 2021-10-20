<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20211020\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey;
/**
 * @api
 *
 * Mimics https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitor/ParentConnectingVisitor.php
 *
 * @see \Symplify\SimplePhpDocParser\Tests\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitorTest
 */
final class ParentConnectingPhpDocNodeVisitor extends \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor
{
    /**
     * @var Node[]
     */
    private $stack = [];
    public function beforeTraverse(\PHPStan\PhpDocParser\Ast\Node $node) : void
    {
        $this->stack = [$node];
    }
    public function enterNode(\PHPStan\PhpDocParser\Ast\Node $node) : \PHPStan\PhpDocParser\Ast\Node
    {
        if ($this->stack !== []) {
            $parentNode = $this->stack[\count($this->stack) - 1];
            $node->setAttribute(\RectorPrefix20211020\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey::PARENT, $parentNode);
        }
        $this->stack[] = $node;
        return $node;
    }
    /**
     * @return null|int|\PhpParser\Node|Node[] Replacement node (or special return
     */
    public function leaveNode(\PHPStan\PhpDocParser\Ast\Node $node)
    {
        \array_pop($this->stack);
        return null;
    }
}
