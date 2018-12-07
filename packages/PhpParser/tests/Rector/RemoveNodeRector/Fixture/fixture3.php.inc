<?php

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Fixture;

class MyAbstractVisitor extends \PhpParser\NodeVisitorAbstract
{

}

class Visitor3 extends MyAbstractVisitor
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return false;
    }
}

?>
-----
<?php

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Fixture;

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

?>
