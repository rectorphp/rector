<?php

declare(strict_types=1);

namespace Rector\Php53\Tests\Rector\Ternary\TernaryToElvisRector;

use Iterator;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TernaryToElvisRectorTest extends AbstractRectorTestCase
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
        return TernaryToElvisRector::class;
    }
}
