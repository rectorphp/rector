<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

class Closure
{
    private function refactorStmts(array $nodes): array
    {
        $this->traverseNodesWithCallable($nodes, function (Node $node) {
            if ($node instanceof Name && $node instanceof Identifier) {
                $this->processNameOrIdentifier($node);
            }

            return;
        });

        return $nodes;
    }
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

class Closure
{
    private function refactorStmts(array $nodes): array
    {
        $this->traverseNodesWithCallable($nodes, function (Node $node) {
            if (!$node instanceof Name) {
                return;
            }
            if (!$node instanceof Identifier) {
                return;
            }
            $this->processNameOrIdentifier($node);
        });

        return $nodes;
    }
}

?>
