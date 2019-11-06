<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SetTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/../../../config/set/database-migration/mysql-to-mysqli.yaml';
    }
}
