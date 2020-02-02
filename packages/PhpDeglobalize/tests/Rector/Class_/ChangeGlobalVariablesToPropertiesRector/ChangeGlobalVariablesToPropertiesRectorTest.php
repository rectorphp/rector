<?php

declare(strict_types=1);

namespace Rector\PhpDeglobalize\Tests\Rector\Class_\ChangeGlobalVariablesToPropertiesRector;

use Iterator;
use Rector\PhpDeglobalize\Rector\Class_\ChangeGlobalVariablesToPropertiesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeGlobalVariablesToPropertiesRectorTest extends AbstractRectorTestCase
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
        return ChangeGlobalVariablesToPropertiesRector::class;
    }
}
