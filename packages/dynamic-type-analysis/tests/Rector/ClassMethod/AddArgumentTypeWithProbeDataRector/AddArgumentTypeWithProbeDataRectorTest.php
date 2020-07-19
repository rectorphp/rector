<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;
use Rector\DynamicTypeAnalysis\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector;
use Rector\DynamicTypeAnalysis\Tests\ProbeStorage\StaticInMemoryProbeStorage;
use Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector\Fixture\SomeClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddArgumentTypeWithProbeDataRectorTest extends AbstractRectorTestCase
{
    /**
     * @var string
     */
    private const METHOD_REFERENCE = SomeClass::class . '::run';

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->initializeProbeData();

        $this->doTestFileInfo($fileInfo);
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
        $staticInMemoryProbeStorage = new StaticInMemoryProbeStorage();
        TypeStaticProbe::setProbeStorage($staticInMemoryProbeStorage);

        $staticInMemoryProbeStorage::clear();

        TypeStaticProbe::recordArgumentType('hey', self::METHOD_REFERENCE, 0);
    }
}
