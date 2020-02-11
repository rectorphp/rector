<?php

namespace Rector\PHPUnit\Tests\Rector\GetMockRector\Fixture;

final class SkipBuildMockTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $secondMock = $this->buildMock()
            ->getMock();
    }
}
