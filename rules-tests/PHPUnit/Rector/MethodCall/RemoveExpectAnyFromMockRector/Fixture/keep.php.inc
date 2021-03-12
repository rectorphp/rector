<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector\Fixture;

use PHPUnit\Framework\TestCase;

class Keep extends TestCase
{
    public function test()
    {
        $translator = $this->createMock('Keep');
        $translator->expects($this->once())
            ->method('trans')
            ->willReturn('translated max {{ max }}!');
    }
}
