<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Property\RemoveTemporaryUuidColumnPropertyRector;

use Iterator;
use Rector\Doctrine\Rector\Property\RemoveTemporaryUuidColumnPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveTemporaryUuidColumnPropertyRectorTest extends AbstractRectorTestCase
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
        return RemoveTemporaryUuidColumnPropertyRector::class;
    }
}
