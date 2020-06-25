<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\BinaryOp\IsIterableRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php71\Rector\BinaryOp\IsIterableRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class IsIterableRectorTest extends AbstractRectorTestCase
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
        return IsIterableRector::class;
    }
}
