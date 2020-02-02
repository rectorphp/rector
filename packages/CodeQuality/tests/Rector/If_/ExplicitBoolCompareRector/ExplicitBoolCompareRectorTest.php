<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector;

use Iterator;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExplicitBoolCompareRectorTest extends AbstractRectorTestCase
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
        return ExplicitBoolCompareRector::class;
    }
}
