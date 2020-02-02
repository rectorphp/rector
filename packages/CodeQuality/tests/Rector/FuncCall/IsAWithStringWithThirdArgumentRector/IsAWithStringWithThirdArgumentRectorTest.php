<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\IsAWithStringWithThirdArgumentRector;

use Iterator;
use Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IsAWithStringWithThirdArgumentRectorTest extends AbstractRectorTestCase
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
        return IsAWithStringWithThirdArgumentRector::class;
    }
}
