<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseVariableNameRector;

use Iterator;
use Rector\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UnderscoreToCamelCaseVariableNameRectorTest extends AbstractRectorTestCase
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
        return UnderscoreToCamelCaseVariableNameRector::class;
    }
}
