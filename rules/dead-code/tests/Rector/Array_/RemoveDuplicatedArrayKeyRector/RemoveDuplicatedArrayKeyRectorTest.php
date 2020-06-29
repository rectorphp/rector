<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Array_\RemoveDuplicatedArrayKeyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveDuplicatedArrayKeyRectorTest extends AbstractRectorTestCase
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
        return RemoveDuplicatedArrayKeyRector::class;
    }
}
