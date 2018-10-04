<?php declare(strict_types=1);

use PhpParser\Node;

class SomeVisitor implements \PhpParser\NodeVisitor
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return \PhpParser\NodeTraverser::REMOVE_NODE;
    }

    public function beforeTraverse(array $nodes)
    {
    }

    public function enterNode(Node $node)
    {
    }

    public function afterTraverse(array $nodes)
    {
    }
}
