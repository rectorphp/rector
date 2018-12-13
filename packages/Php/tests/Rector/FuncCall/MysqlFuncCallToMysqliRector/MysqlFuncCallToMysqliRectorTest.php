<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\MysqlFuncCallToMysqliRector;

use Rector\Php\Rector\FuncCall\MysqlFuncCallToMysqliRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MysqlFuncCallToMysqliRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return MysqlFuncCallToMysqliRector::class;
    }
}
