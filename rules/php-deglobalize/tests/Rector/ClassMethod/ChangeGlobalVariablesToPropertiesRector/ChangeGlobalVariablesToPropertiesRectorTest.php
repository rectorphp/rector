<?php

declare(strict_types=1);

namespace Rector\PhpDeglobalize\Tests\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PhpDeglobalize\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeGlobalVariablesToPropertiesRectorTest extends AbstractRectorTestCase
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
        return ChangeGlobalVariablesToPropertiesRector::class;
    }
}
