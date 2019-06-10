<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

class SkipExtraContent
{
    public function prettyPrint(array $stmts): string
    {
        $stmts = $stmts + [1];

        return parent::prettyPrint($stmts);
    }
}
