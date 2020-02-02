<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector;

use Iterator;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedPrivatePropertyRectorTest extends AbstractRectorTestCase
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
        return RemoveUnusedPrivatePropertyRector::class;
    }
}
