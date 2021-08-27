<?php

namespace Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use PHPUnit\Framework\TestCase;

class SkipCreateMock extends TestCase
{
    public function test()
    {
        $this->createMock(\Foo::class);
    }
}

?>
