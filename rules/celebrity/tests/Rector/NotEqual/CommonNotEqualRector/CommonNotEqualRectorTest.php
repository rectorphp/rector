<?php

declare(strict_types=1);

namespace Rector\Celebrity\Tests\Rector\NotEqual\CommonNotEqualRector;

use Iterator;
use Rector\Celebrity\Rector\NotEqual\CommonNotEqualRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
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

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return CommonNotEqualRector::class;
    }
}
