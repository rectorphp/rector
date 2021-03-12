<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\Tests\Rector\Property\RemoveUselessVarTagRector;

use Iterator;
use Rector\DeadDocBlock\Rector\Property\RemoveUselessVarTagRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveUselessVarTagRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.4
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

    protected function getRectorClass(): string
    {
        return RemoveUselessVarTagRector::class;
    }
}
