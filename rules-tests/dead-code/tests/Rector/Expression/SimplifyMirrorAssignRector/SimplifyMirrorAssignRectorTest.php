<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Expression\SimplifyMirrorAssignRector;

use Iterator;
use Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SimplifyMirrorAssignRectorTest extends AbstractRectorTestCase
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
        return SimplifyMirrorAssignRector::class;
    }
}
