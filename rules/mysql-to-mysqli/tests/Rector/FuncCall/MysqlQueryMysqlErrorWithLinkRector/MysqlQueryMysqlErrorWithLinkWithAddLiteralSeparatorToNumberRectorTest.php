<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MysqlToMysqli\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Symplify\SmartFileSystem\SmartFileInfo;

// TODO: move to more appropriate place
final class MysqlQueryMysqlErrorWithLinkWithAddLiteralSeparatorToNumberRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/CornerCaseFixture');
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddLiteralSeparatorToNumberRector::class => null,
            MysqlQueryMysqlErrorWithLinkRector::class => null,
        ];
    }
}
