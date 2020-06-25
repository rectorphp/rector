<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveUnusedParameterRectorTest extends AbstractRectorTestCase
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
        return RemoveUnusedParameterRector::class;
    }
}
