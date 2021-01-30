<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\Tests\Rector\Property\RemoveUselessVarTagRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUselessVarTagRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.4
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return \Rector\DeadDocBlock\Rector\Property\RemoveUselessVarTagRector::class;
    }
}
