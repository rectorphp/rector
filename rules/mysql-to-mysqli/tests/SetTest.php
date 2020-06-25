<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetTest extends AbstractRectorTestCase
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

    protected function provideConfig(): string
    {
        return __DIR__ . '/../../../config/set/database-migration/mysql-to-mysqli.yaml';
    }
}
