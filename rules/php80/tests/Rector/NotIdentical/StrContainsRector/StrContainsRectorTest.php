<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StrContainsRectorTest extends AbstractRectorTestCase
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
        return StrContainsRector::class;
    }
}
