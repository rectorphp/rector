<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\DecorateMethodWithArgumentTypeProbeRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DynamicTypeAnalysis\Rector\ClassMethod\DecorateMethodWithArgumentTypeProbeRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DecorateMethodWithArgumentTypeProbeRectorTest extends AbstractRectorTestCase
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
        return DecorateMethodWithArgumentTypeProbeRector::class;
    }
}
