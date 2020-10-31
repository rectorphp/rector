<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector;

use Iterator;
use Rector\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveDuplicatedInstanceOfRectorTest extends AbstractRectorTestCase
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
        return RemoveDuplicatedInstanceOfRector::class;
    }
}
