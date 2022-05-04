<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\PostRector\Collector\NodesToReplaceCollector;

final class SkipForeachCallableNoScope
{
    public function run(NodesToReplaceCollector $nodesToReplaceCollector, \PhpParser\Node $node)
    {
        foreach ($nodesToReplaceCollector->getNodes() as [$nodeToFind, $replacement]) {
            if ($node === $nodeToFind) {
                return $replacement;
            }
        }
    }
}
