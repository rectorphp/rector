<?php

declare(strict_types=1);

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StringClassNameToClassConstantRectorTest extends AbstractRectorTestCase
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
        return StringClassNameToClassConstantRector::class;
    }
}
