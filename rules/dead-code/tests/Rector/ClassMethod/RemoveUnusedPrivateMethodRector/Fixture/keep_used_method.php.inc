<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;

final class KeepUsedMethod
{
    public function processArgumentPosition(Node $node, int $argumentPosition): ?Node
    {
        $this->findPreviousNodeAssign($node, $argumentPosition);

        return $node;
    }

    private function findPreviousNodeAssign(Node $node, Node $firstArgument): ?Assign
    {
        return $this->betterNodeFinder->findFirstPrevious($node, function (Node $checkedNode) use ($firstArgument) {
        });
    }
}
