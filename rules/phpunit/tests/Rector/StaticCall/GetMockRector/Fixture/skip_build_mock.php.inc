<?php

namespace Rector\PHPUnit\Tests\Rector\StaticCall\GetMockRector\Fixture;

final class SkipBuildMockTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $secondMock = $this->buildMock()
            ->getMock();
    }
}
