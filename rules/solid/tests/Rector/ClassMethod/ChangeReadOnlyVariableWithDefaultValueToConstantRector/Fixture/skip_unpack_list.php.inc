<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipUnpackList
{
    public function refactor(Node $node)
    {
        if (is_array($useImportToRestore)) {
            list($import, $alias) = $useImportToRestore;
        } else {
            $alias = '';
        }

        return $this->addImportToNamespaceIfMissing($node, $import, $alias);
    }
}
