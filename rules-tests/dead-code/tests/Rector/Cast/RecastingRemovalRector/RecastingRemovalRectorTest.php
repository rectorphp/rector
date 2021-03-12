<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Cast\RecastingRemovalRector;

use Iterator;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RecastingRemovalRectorTest extends AbstractRectorTestCase
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
        return RecastingRemovalRector::class;
    }
}
