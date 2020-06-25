<?php

declare(strict_types=1);

namespace Rector\Celebrity\Tests\Rector\BooleanOp\LogicalToBooleanRector;

use Iterator;
use Rector\Celebrity\Rector\BooleanOp\LogicalToBooleanRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class LogicalToBooleanRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
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
