<?php

declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector;
use Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector;

final class NetteTesterPHPUnitRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        // prepare dummy data
        FileSystem::copy(__DIR__ . '/Copy', $this->getTempPath());

        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            NetteAssertToPHPUnitAssertRector::class => [],
            NetteTesterClassToPHPUnitClassRector::class => [],
        ];
    }
}
