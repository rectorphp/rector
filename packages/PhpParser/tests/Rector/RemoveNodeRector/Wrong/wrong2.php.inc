<?php declare(strict_types=1);

class SomeVisitor implements \PhpParser\NodeVisitor
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return false;
    }
}
