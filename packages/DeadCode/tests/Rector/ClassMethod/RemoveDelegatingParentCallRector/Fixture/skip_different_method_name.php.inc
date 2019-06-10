<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

class SkipDifferentMethodName
{
    public function prettyPrint(array $stmts): string
    {
        return parent::prettyPrintThat($stmts);
    }
}
