<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\TestListenerToHooksRector;

use Iterator;
use Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TestListenerToHooksRectorTest extends AbstractRectorTestCase
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
        return TestListenerToHooksRector::class;
    }
}
