<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DynamicTypeAnalysis\Probe\ProbeStaticStorage;
use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;
use Rector\DynamicTypeAnalysis\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector;
use Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector\Fixture\SomeClass;

final class AddArgumentTypeWithProbeDataRectorTest extends AbstractRectorTestCase
{
    /**
     * @var string
     */
    private const METHOD_REFERENCE = SomeClass::class . '::run';

    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->initializeProbeData();

        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return AddArgumentTypeWithProbeDataRector::class;
    }

    private function initializeProbeData(): void
    {
        // clear cache
        ProbeStaticStorage::clear();

        TypeStaticProbe::recordArgumentType('hey', self::METHOD_REFERENCE, 0);
    }
}
