<?php declare(strict_types=1);

namespace Rector\PhpParser\Tests\Rector\RemoveNodeRector\Wrong;

class Visitor4 extends \PhpParser\NodeVisitorAbstract
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return FALSE;
    }
}
