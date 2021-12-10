<?php

declare (strict_types=1);
namespace PhpParser;

/**
 * @codeCoverageIgnore
 */
class NodeVisitorAbstract implements \PhpParser\NodeVisitor
{
    /**
     * @param mixed[] $nodes
     */
    public function beforeTraverse($nodes)
    {
        return null;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function enterNode($node)
    {
        return null;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function leaveNode($node)
    {
        return null;
    }
    /**
     * @param mixed[] $nodes
     */
    public function afterTraverse($nodes)
    {
        return null;
    }
}
