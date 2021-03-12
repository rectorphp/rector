<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\CreateMockToCreateStubRector\Fixture;

use PHPUnit\Framework\TestCase;

class SkipSonataExpects extends TestCase
{
    public function testItBehavesAsExpected(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);

        $datagrid->expects($this->once())
            ->method('getPager')
            ->willReturn($pager);
    }
}
