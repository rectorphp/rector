<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\New_\NewStaticToNewSelfRector;

use Iterator;
use Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NewStaticToNewSelfRectorTest extends AbstractRectorTestCase
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
        return NewStaticToNewSelfRector::class;
    }
}
