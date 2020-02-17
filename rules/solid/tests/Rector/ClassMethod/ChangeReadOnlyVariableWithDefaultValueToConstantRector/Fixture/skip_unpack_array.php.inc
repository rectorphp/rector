<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipUnpackArray
{
    public function refactor(Node $node)
    {
        if (is_array($useImportToRestore)) {
            [$import, $alias] = $useImportToRestore;
        } else {
            $alias = '';
        }

        return $this->addImportToNamespaceIfMissing($node, $import, $alias);
    }
}
