<?php

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Fixture;

use PhpParser\Node;

class SomeVisitor2 implements \PhpParser\NodeVisitor
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return false;
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

?>
-----
<?php

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Fixture;

use PhpParser\Node;

class SomeVisitor2 implements \PhpParser\NodeVisitor
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

?>
