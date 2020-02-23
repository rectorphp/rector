<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MysqlToMysqli\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector;

final class MysqlQueryMysqlErrorWithLinkRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return MysqlQueryMysqlErrorWithLinkRector::class;
    }
}
