<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\BooleanOp\LogicalToBooleanRector;

use Iterator;
use Rector\CodeQuality\Rector\BooleanOp\LogicalToBooleanRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class LogicalToBooleanRectorTest extends AbstractRectorTestCase
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
        return LogicalToBooleanRector::class;
    }
}
