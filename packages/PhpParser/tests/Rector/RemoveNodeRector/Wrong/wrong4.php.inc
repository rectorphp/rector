<?php

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Wrong;

class Visitor4 extends \PhpParser\NodeVisitorAbstract
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return FALSE;
    }
}

?>
-----
<?php

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Wrong;

class Visitor4 extends \PhpParser\NodeVisitorAbstract
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return \PhpParser\NodeTraverser::REMOVE_NODE;
    }
}

?>
