<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\ExceptionAnnotationRector;

use Iterator;
use Rector\PHPUnit\Rector\ExceptionAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExceptionAnnotationRectorTest extends AbstractRectorTestCase
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
        return ExceptionAnnotationRector::class;
    }
}
