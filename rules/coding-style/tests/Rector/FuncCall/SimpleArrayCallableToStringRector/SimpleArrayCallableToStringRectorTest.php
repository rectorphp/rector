<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\SimpleArrayCallableToStringRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\SimpleArrayCallableToStringRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class SimpleArrayCallableToStringRectorTest extends AbstractRectorTestCase
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
        return SimpleArrayCallableToStringRector::class;
    }
}
