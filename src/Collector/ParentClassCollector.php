<?php

declare (strict_types=1);
namespace Rector\Core\Collector;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
/**
 * @implements Collector<Class_, string[]|null>
 */
final class ParentClassCollector implements Collector
{
    public function getNodeType() : string
    {
        return Class_::class;
    }
    /**
     * @param Class_ $node
     * @return string[]|null
     */
    public function processNode(Node $node, Scope $scope) : ?array
    {
        if (!$node->extends instanceof Name) {
            return null;
        }
        return [$node->extends->toString()];
    }
}
