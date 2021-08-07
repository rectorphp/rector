<?php

namespace Rector\Tests\DeadCode\Rector\Expression\RemoveDeadStmtRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\Tests\DeadCode\Rector\Expression\RemoveDeadStmtRector\Source\Entity;

class SkipHasGetMagic extends TestCase
{
    public function testThrow()
    {
        $obj = new Entity();
        $this->expectException(InvalidArgumentException::class);

        $obj->invalid;
    }
}
