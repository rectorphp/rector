<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

final class SkipInsideDoWhileNextNotStmt
{
    public function run($foo)
    {
        do {
            if ($foo) {

            }
        } while ($this->foo);
    }
}
