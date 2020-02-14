<?php

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

use PHPUnit\Framework\TestCase;

class SkipPhpUnit extends TestCase
{
    public function testMe()
    {
        $this->assertTrue(True);
        $this->markTestSkipped('uups');
    }
}
