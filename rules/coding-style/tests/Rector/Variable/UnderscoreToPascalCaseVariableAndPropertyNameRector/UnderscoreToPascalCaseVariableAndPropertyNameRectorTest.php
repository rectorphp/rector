<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToPascalCaseVariableAndPropertyNameRector;

use Iterator;
use Rector\CodingStyle\Rector\Variable\UnderscoreToPascalCaseVariableAndPropertyNameRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class UnderscoreToPascalCaseVariableAndPropertyNameRectorTest extends AbstractRectorTestCase
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
        return UnderscoreToPascalCaseVariableAndPropertyNameRector::class;
    }
}
