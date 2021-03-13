<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\NotEqual\CommonNotEqualRector;

use Iterator;
use Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CommonNotEqualRectorTest extends AbstractRectorTestCase
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
        return CommonNotEqualRector::class;
    }
}
