<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\DecorateMethodWithArgumentTypeProbeRector;

use Iterator;
use Rector\DynamicTypeAnalysis\Rector\ClassMethod\DecorateMethodWithArgumentTypeProbeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class DecorateMethodWithArgumentTypeProbeRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return DecorateMethodWithArgumentTypeProbeRector::class;
    }
}
