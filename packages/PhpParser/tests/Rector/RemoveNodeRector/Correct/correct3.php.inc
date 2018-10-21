<?php declare(strict_types=1);

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Wrong;

class MyAbstractVisitor extends \PhpParser\NodeVisitorAbstract
{

}

class Visitor3 extends MyAbstractVisitor
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return \PhpParser\NodeTraverser::REMOVE_NODE;
    }
}
