<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\Class_\ChangeLocalPropertyToVariableRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeLocalPropertyToVariableRectorTest extends AbstractRectorTestCase
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
        return ChangeLocalPropertyToVariableRector::class;
    }
}
