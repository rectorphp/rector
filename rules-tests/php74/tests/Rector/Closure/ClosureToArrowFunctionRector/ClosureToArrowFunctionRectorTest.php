<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Closure\ClosureToArrowFunctionRector;

use Iterator;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ClosureToArrowFunctionRectorTest extends AbstractRectorTestCase
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
        return ClosureToArrowFunctionRector::class;
    }
}
