<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;

use Iterator;
use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveRepositoryFromEntityAnnotationRectorTest extends AbstractRectorTestCase
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
        return RemoveRepositoryFromEntityAnnotationRector::class;
    }
}
