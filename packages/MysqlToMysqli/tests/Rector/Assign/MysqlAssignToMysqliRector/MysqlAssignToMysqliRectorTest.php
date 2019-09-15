<?php declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Rector\Assign\MysqlAssignToMysqliRector;

use Rector\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MysqlAssignToMysqliRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return MysqlAssignToMysqliRector::class;
    }
}
