<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveUnusedDoctrineEntityMethodAndPropertyRectorTest extends AbstractRectorTestCase
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
        return RemoveUnusedDoctrineEntityMethodAndPropertyRector::class;
    }
}
