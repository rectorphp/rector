<?php

namespace Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

use PHPUnit\Framework\TestCase;

class SkipPhpUnit extends TestCase
{
    public function testMe()
    {
        $this->assertTrue(True);
        $this->markTestSkipped('uups');
    }
}
