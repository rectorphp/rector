<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector;

use Iterator;
use Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EndsWithFunctionToNetteUtilsStringsRectorTest extends AbstractRectorTestCase
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
        return EndsWithFunctionToNetteUtilsStringsRector::class;
    }
}
