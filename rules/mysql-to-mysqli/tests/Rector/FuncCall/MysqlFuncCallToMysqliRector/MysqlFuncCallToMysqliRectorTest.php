<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlFuncCallToMysqliRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MysqlFuncCallToMysqliRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return MysqlFuncCallToMysqliRector::class;
    }
}
