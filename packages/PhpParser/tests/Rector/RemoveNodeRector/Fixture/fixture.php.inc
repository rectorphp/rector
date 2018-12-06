<?php

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Wrong;

class SomeVisitor extends \PhpParser\NodeVisitorAbstract
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return false;
    }
}

?>
-----
<?php

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Wrong;

class SomeVisitor extends \PhpParser\NodeVisitorAbstract
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return \PhpParser\NodeTraverser::REMOVE_NODE;
    }
}

?>
