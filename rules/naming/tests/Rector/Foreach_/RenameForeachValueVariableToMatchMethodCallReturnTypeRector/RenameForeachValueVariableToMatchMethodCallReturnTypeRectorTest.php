<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;

use Iterator;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameForeachValueVariableToMatchMethodCallReturnTypeRectorTest extends AbstractRectorTestCase
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
        return RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class;
    }
}
