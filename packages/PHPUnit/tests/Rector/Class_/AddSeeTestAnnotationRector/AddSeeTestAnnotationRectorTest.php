<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector;

use Iterator;
use Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddSeeTestAnnotationRectorTest extends AbstractRectorTestCase
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
        return AddSeeTestAnnotationRector::class;
    }
}
