<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\AddProphecyTraitRector;

use Iterator;
use Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddProphecyTraitRectorTest extends AbstractRectorTestCase
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
        return AddProphecyTraitRector::class;
    }
}
