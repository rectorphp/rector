<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector;

use Iterator;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ExplicitBoolCompareRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ExplicitBoolCompareRector::class;
    }
}
