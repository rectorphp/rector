<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

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

    private function resolveDependentFiles(Node $node, MutatingScope $mutatingScope): void
    {
        if (! $this->configuration->isCacheEnabled()) {
            return;
        }

        foreach ($this->dependencyResolver->resolveDependencies($node, $mutatingScope) as $dependentFile) {
            $this->dependentFiles[] = $dependentFile;
        }
    }
}
