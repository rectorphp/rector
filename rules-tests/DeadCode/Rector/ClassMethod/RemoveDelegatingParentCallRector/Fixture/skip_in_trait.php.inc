<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

trait SkipInTrait
{
    public function prettyPrint(array $stmts): string
    {
        return parent::prettyPrint($stmts);
    }
}
