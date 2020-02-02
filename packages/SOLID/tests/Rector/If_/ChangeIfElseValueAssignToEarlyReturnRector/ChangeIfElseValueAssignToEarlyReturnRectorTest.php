<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;

use Iterator;
use Rector\SOLID\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeIfElseValueAssignToEarlyReturnRectorTest extends AbstractRectorTestCase
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
        return ChangeIfElseValueAssignToEarlyReturnRector::class;
    }
}
