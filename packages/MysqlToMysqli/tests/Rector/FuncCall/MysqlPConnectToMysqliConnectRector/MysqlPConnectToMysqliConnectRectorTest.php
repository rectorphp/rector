<?php declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlPConnectToMysqliConnectRector;

use Rector\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MysqlPConnectToMysqliConnectRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return MysqlPConnectToMysqliConnectRector::class;
    }
}
