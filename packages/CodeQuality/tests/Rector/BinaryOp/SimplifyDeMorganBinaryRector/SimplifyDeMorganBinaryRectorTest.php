<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\BinaryOp\SimplifyDeMorganBinaryRector;

use Iterator;
use Rector\CodeQuality\Rector\BinaryOp\SimplifyDeMorganBinaryRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyDeMorganBinaryRectorTest extends AbstractRectorTestCase
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
        return SimplifyDeMorganBinaryRector::class;
    }
}
