<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

class SkipChangedArguments
{
    public function prettyPrint(array $stmts): string
    {
        return parent::prettyPrint($stmts, false);
    }
}
