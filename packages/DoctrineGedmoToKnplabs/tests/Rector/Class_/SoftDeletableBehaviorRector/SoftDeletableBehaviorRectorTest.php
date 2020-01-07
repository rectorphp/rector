<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\SoftDeletableBehaviorRector;

use Iterator;
use Rector\DoctrineGedmoToKnplabs\Rector\Class_\SoftDeletableBehaviorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SoftDeletableBehaviorRectorTest extends AbstractRectorTestCase
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
        return SoftDeletableBehaviorRector::class;
    }
}
