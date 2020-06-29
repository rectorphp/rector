<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\SOLID\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeReadOnlyVariableWithDefaultValueToConstantRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return ChangeReadOnlyVariableWithDefaultValueToConstantRector::class;
    }
}
