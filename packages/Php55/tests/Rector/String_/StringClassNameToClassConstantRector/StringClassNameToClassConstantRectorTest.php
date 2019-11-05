<?php

declare(strict_types=1);

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector;

use Iterator;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringClassNameToClassConstantRectorTest extends AbstractRectorTestCase
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
        return StringClassNameToClassConstantRector::class;
    }
}
