<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Tests\Rector\StaticCall\RemoveArgumentTypeProbeRector;

use Iterator;
use Rector\DynamicTypeAnalysis\Rector\StaticCall\RemoveArgumentTypeProbeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveArgumentTypeProbeRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
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
