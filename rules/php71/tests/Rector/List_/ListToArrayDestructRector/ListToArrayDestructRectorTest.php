<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\List_\ListToArrayDestructRector;

use Iterator;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ListToArrayDestructRectorTest extends AbstractRectorTestCase
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
        return ListToArrayDestructRector::class;
    }
}
