<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use PhpParser\PrettyPrinter\Standard;

class SkipChangedArguments extends Standard
{
    public function prettyPrint(array $stmts): string
    {
        return parent::prettyPrint($stmts, false);
    }
}
