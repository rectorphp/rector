<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\Catch_\RemoveUnusedVariableInCatchRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;

final class RemoveUnusedVariableInCatchRectorTest extends AbstractRectorTestCase
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
        return RemoveUnusedVariableInCatchRector::class;
    }
}
