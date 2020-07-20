<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Set\ValueObject\SetList;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetTest extends AbstractRectorTestCase
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

    protected function provideSet(): string
    {
        return SetList::MYSQL_TO_MYSQLI;
    }
}
