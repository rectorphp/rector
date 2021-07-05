<?php

declare (strict_types=1);
namespace RectorPrefix20210705\Symplify\SimplePhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20210705\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey;
/**
 * Mimics https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitor/ParentConnectingVisitor.php
 *
 * @see \Symplify\SimplePhpDocParser\Tests\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitorTest
 */
final class ParentConnectingPhpDocNodeVisitor extends \RectorPrefix20210705\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor
{
    /**
     * @var Node[]
     */
    private $stack = [];
    /**
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function beforeTraverse($node) : void
    {
        $this->stack = [$node];
    }
    /**
     * @return int|Node|null
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function enterNode($node)
    {
        if ($this->stack !== []) {
            $parentNode = $this->stack[\count($this->stack) - 1];
            $node->setAttribute(\RectorPrefix20210705\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey::PARENT, $parentNode);
        }
        $this->stack[] = $node;
        return $node;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function leaveNode($node) : void
    {
        \array_pop($this->stack);
    }
}
