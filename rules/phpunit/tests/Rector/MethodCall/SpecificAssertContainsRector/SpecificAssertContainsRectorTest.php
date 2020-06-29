<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SpecificAssertContainsRectorTest extends AbstractRectorTestCase
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
        return SpecificAssertContainsRector::class;
    }
}
