<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\If_\NullableCompareToNullRector;

use Iterator;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NullableCompareToNullRectorTest extends AbstractRectorTestCase
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
        return NullableCompareToNullRector::class;
    }
}
