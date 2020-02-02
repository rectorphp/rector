<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Rector\Assign\MysqlAssignToMysqliRector;

use Iterator;
use Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MysqlAssignToMysqliRectorTest extends AbstractRectorTestCase
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
        return MysqlAssignToMysqliRector::class;
    }
}
