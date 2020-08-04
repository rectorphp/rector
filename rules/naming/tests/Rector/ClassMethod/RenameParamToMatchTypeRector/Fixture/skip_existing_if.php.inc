<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;

class SkipExistingIf
{
    private function shortenElseIf(If_ $node): ?Node
    {
        if ($node->else === null) {
            return null;
        }

        $if = $node->else->stmts[0];
    }
}
