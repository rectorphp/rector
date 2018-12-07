<?php

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Fixture;

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

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Fixture;

class Visitor4 extends \PhpParser\NodeVisitorAbstract
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return \PhpParser\NodeTraverser::REMOVE_NODE;
    }
}

?>
