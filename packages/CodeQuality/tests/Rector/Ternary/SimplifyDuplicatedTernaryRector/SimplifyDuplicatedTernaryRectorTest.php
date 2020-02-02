<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Ternary\SimplifyDuplicatedTernaryRector;

use Iterator;
use Rector\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyDuplicatedTernaryRectorTest extends AbstractRectorTestCase
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
        return SimplifyDuplicatedTernaryRector::class;
    }
}
