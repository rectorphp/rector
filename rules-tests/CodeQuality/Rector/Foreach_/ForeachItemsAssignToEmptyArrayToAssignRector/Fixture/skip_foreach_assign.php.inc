<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

use PhpParser\Node;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Dependency\DependencyResolver;

class SkipForeachAssign
{
    /**
     * @var mixed[]
     */
    private $dependentFiles = [];

    public function processNodes()
    {
        $this->dependentFiles = [];
    }

    private function resolveDependentFiles(
        DependencyResolver $dependencyResolver,
        Node $node,
        MutatingScope $mutatingScope
    ): void {
        foreach ($dependencyResolver->resolveDependencies($node, $mutatingScope) as $dependentFile) {
            $this->dependentFiles[] = $dependentFile;
        }
    }
}
