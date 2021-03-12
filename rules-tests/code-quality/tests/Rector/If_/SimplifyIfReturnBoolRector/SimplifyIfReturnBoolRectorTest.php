<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector;

use Iterator;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SimplifyIfReturnBoolRectorTest extends AbstractRectorTestCase
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
        return SimplifyIfReturnBoolRector::class;
    }
}
