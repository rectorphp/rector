<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\AbstractNodeVisitor;
use PHPStan\PhpDocParser\Ast\Node;
use Rector\PhpDocParser\PhpDocParser\ValueObject\PhpDocAttributeKey;
/**
 * Mimics https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeVisitor/ParentConnectingVisitor.php
 *
 * @see \Rector\Tests\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitorTest
 */
final class ParentConnectingPhpDocNodeVisitor extends AbstractNodeVisitor
{
    /**
     * @var Node[]
     */
    private array $stack = [];
    public function beforeTraverse(array $nodes): ?array
    {
        $this->stack = [];
        return null;
    }
    public function enterNode(Node $node): Node
    {
        if ($this->stack !== []) {
            $parentNode = $this->stack[count($this->stack) - 1];
            $node->setAttribute(PhpDocAttributeKey::PARENT, $parentNode);
        }
        $this->stack[] = $node;
        return $node;
    }
    /**
     * @return null
     */
    public function leaveNode(Node $node)
    {
        array_pop($this->stack);
        return null;
    }
}
