<?php

declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\Class_\InlineValidationRulesToArrayDefinitionRector;

use Iterator;
use Rector\Laravel\Rector\Class_\InlineValidationRulesToArrayDefinitionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class InlineValidationRulesToArrayDefinitionRectorTest extends AbstractRectorTestCase
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
        return InlineValidationRulesToArrayDefinitionRector::class;
    }
}
