<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Include_\AbsolutizeRequireAndIncludePathRector;

use Iterator;
use Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AbsolutizeRequireAndIncludePathRectorTest extends AbstractRectorTestCase
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
        return AbsolutizeRequireAndIncludePathRector::class;
    }
}
