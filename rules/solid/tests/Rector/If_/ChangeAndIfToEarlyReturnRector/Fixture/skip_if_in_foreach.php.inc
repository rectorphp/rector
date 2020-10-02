<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

class SkipIfInForeachClass
{
    private function refactorStmts(array $nodes): void
    {
        foreach ($nodes as $node) {
            if ($node instanceof Name && $node instanceof Identifier) {
                $this->processNameOrIdentifier($node);
            }

            return;
        }

        return;
    }
}

?>
