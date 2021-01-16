<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipReferenceDirect
{
    public function removeAndCollectFormerAssignedVariables(Class_ $class, bool $skipSetUpMethod = true): array
    {
        $formerVariablesByMethods = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            &$formerVariablesByMethods,
            $skipSetUpMethod
        ): ?PropertyFetch {
            $this->processAssign($node, $parentNode, $type, $formerVariablesByMethods);
        });
    }
}
