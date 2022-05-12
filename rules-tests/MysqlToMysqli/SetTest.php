<?php

declare(strict_types=1);

namespace Rector\Tests\MysqlToMysqli;

use Iterator;
use Rector\Set\ValueObject\SetList;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->markTestSkipped(
            '@todo update rules to handle both rename + argumentt adding at the same time; otherwise lost during scope update'
        );

        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return SetList::MYSQL_TO_MYSQLI;
    }
}
