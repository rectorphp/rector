<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;

final class ForeachToInArrayRector
{
    public function refactor(Node $node)
    {
        [, $comparedNode] = $matchedNodes;
    }
}
