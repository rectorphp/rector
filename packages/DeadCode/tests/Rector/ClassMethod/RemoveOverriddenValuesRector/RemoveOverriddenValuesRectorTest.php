<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector;

use Iterator;
use Rector\DeadCode\Rector\ClassMethod\RemoveOverriddenValuesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveOverriddenValuesRectorTest extends AbstractRectorTestCase
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
        return RemoveOverriddenValuesRector::class;
    }
}
