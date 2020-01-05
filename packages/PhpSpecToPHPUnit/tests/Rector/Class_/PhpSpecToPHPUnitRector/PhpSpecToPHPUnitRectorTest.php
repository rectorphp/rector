<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector;

use Iterator;
use Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\MockVariableToPropertyFetchRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PhpSpecToPHPUnitRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return string[][]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            # 1. first convert mocks
            PhpSpecMocksToPHPUnitMocksRector::class => [],
            PhpSpecPromisesToPHPUnitAssertRector::class => [],
            PhpSpecMethodToPHPUnitMethodRector::class => [],
            PhpSpecClassToPHPUnitClassRector::class => [],
            AddMockPropertiesRector::class => [],
            MockVariableToPropertyFetchRector::class => [],
        ];
    }
}
