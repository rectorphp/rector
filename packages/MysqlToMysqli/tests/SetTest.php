<?php declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests;

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

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/SetFixture.php.inc'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/../../../config/set/database-migration/mysql-to-mysqli.yaml';
    }
}
