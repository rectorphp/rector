<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

final class SkipWithNumber
{
    public function run(array $psr4NamespacesToPaths)
    {
        foreach ($psr4NamespacesToPaths as $psr4NamespaceToPath) {
            continue;
        }
    }
}
