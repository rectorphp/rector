<?php

declare(strict_types=1);

namespace Rector\Tests\Symfony\Rector\Return_\SimpleFunctionAndFilterRector;

use Iterator;
use Rector\Symfony\Rector\Return_\SimpleFunctionAndFilterRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SimpleFunctionAndFilterRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<mixed, SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return SimpleFunctionAndFilterRector::class;
    }
}
