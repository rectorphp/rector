<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector;
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
