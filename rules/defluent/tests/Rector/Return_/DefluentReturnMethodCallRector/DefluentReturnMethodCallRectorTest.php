<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\Return_\DefluentReturnMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Defluent\Rector\Return_\DefluentReturnMethodCallRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DefluentReturnMethodCallRectorTest extends AbstractRectorTestCase
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
        return DefluentReturnMethodCallRector::class;
    }
}
