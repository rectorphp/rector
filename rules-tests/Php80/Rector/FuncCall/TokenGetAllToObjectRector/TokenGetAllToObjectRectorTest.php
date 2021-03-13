<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\FuncCall\TokenGetAllToObjectRector;

use Iterator;
use Rector\Php80\Rector\FuncCall\TokenGetAllToObjectRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TokenGetAllToObjectRectorTest extends AbstractRectorTestCase
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
        return TokenGetAllToObjectRector::class;
    }
}
