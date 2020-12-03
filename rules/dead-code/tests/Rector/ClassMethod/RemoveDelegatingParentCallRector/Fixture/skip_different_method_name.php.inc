<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use PhpParser\PrettyPrinter\Standard;

class SkipDifferentMethodName extends Standard
{
    public function prettyPrint(array $stmts): string
    {
        return parent::prettyPrintThat($stmts);
    }
}
