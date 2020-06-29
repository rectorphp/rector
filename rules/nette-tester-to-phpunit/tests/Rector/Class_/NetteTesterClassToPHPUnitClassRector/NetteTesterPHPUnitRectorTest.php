<?php

declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector;
use Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NetteTesterPHPUnitRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
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
