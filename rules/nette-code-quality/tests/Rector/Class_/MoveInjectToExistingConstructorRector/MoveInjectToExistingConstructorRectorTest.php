<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteCodeQuality\Rector\Class_\MoveInjectToExistingConstructorRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MoveInjectToExistingConstructorRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return MoveInjectToExistingConstructorRector::class;
    }
}
