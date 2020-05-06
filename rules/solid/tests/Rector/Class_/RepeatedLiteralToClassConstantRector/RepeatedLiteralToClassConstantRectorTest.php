<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\SOLID\Rector\Class_\RepeatedLiteralToClassConstantRector;

final class RepeatedLiteralToClassConstantRectorTest extends AbstractRectorTestCase
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
        return RepeatedLiteralToClassConstantRector::class;
    }
}
