<?php

namespace Rector\Tests\DeadCode\Rector\Expression\RemoveDeadStmtRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\Tests\DeadCode\Rector\Expression\RemoveDeadStmtRector\Source\SomeArrayAccess;

class SkipImplementsArrayAccess extends TestCase
{
    public function testThrow()
    {
        $obj = new SomeArrayAccess();
        $this->expectException(InvalidArgumentException::class);

        $obj['test'];
    }
}
