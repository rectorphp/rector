<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadConstructorRector;

use Iterator;
use Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadConstructorRectorTest extends AbstractRectorTestCase
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
        return RemoveDeadConstructorRector::class;
    }
}
