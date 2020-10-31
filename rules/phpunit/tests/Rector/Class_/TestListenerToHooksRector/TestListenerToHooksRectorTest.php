<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\TestListenerToHooksRector;

use Iterator;
use Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TestListenerToHooksRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return TestListenerToHooksRector::class;
    }
}
