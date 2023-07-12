<?php

namespace Rector\PHPUnit\Tests\PHPUnit50\Rector\StaticCall\GetMockRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipPrivateGetMock extends TestCase
{
    public function test()
    {
        $firstMock = $this->getMock('some_class');
    }

    private function getMock(string $className)
    {
    }
}
