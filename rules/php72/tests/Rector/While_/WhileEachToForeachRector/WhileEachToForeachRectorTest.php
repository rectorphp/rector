<?php

declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\While_\WhileEachToForeachRector;

use Iterator;
use Rector\Php72\Rector\While_\WhileEachToForeachRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @requires PHP < 8.0
 */
final class WhileEachToForeachRectorTest extends AbstractRectorTestCase
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
        return WhileEachToForeachRector::class;
    }
}
