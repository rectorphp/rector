<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use PhpParser\PrettyPrinter\Standard;

class SkipExtraContent extends Standard
{
    public function prettyPrint(array $stmts): string
    {
        $stmts = $stmts + [1];

        return parent::prettyPrint($stmts);
    }
}
