<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\LoggableBehaviorRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DoctrineGedmoToKnplabs\Rector\Class_\LoggableBehaviorRector;

final class LoggableBehaviorRectorTest extends AbstractRectorTestCase
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
        return LoggableBehaviorRector::class;
    }
}
