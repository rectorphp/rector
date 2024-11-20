<?php

declare (strict_types=1);
namespace PhpParser;

/**
 * @codeCoverageIgnore
 */
abstract class NodeVisitorAbstract implements \PhpParser\NodeVisitor
{
    public function beforeTraverse(array $nodes)
    {
        return null;
    }
    public function enterNode(\PhpParser\Node $node)
    {
        return null;
    }
    public function leaveNode(\PhpParser\Node $node)
    {
        return null;
    }
    public function afterTraverse(array $nodes)
    {
        return null;
    }
}
