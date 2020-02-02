<?php

declare(strict_types=1);

namespace Rector\MinimalScope\Tests\Rector\Class_\ChangeLocalPropertyToVariableRector;

use Iterator;
use Rector\MinimalScope\Rector\Class_\ChangeLocalPropertyToVariableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeLocalPropertyToVariableRectorTest extends AbstractRectorTestCase
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
        return ChangeLocalPropertyToVariableRector::class;
    }
}
