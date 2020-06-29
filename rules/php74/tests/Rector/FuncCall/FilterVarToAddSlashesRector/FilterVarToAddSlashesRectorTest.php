<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\FuncCall\FilterVarToAddSlashesRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FilterVarToAddSlashesRectorTest extends AbstractRectorTestCase
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
        return FilterVarToAddSlashesRector::class;
    }
}
