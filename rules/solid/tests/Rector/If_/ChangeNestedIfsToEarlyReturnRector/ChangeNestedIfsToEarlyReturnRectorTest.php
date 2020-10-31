<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector;

use Iterator;
use Rector\SOLID\Rector\If_\ChangeNestedIfsToEarlyReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeNestedIfsToEarlyReturnRectorTest extends AbstractRectorTestCase
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
        return ChangeNestedIfsToEarlyReturnRector::class;
    }
}
