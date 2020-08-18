<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\Class_\ChangeSingletonToServiceRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Legacy\Rector\Class_\ChangeSingletonToServiceRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeSingletonToServiceRectorTest extends AbstractRectorTestCase
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
        return ChangeSingletonToServiceRector::class;
    }
}
