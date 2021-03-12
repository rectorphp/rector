<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\NotIdentical\StrposToStringsContainsRector;

use Iterator;
use Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StrposToStringsContainsRectorTest extends AbstractRectorTestCase
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
        return StrposToStringsContainsRector::class;
    }
}
