<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Return_\SimpleFunctionAndFilterRector;

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

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return SimpleFunctionAndFilterRector::class;
    }
}
