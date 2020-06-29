<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Tests\Rector\StaticCall\RemoveArgumentTypeProbeRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DynamicTypeAnalysis\Rector\StaticCall\RemoveArgumentTypeProbeRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveArgumentTypeProbeRectorTest extends AbstractRectorTestCase
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
        return RemoveArgumentTypeProbeRector::class;
    }
}
