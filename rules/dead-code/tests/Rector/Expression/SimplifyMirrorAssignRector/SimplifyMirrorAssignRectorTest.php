<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Expression\SimplifyMirrorAssignRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SimplifyMirrorAssignRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
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
