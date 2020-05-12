<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\Class_\ChangeLocalPropertyToVariableRector;

use MIterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector;

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
