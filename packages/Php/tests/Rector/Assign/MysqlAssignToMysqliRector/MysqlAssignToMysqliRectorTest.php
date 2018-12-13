<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Assign\MysqlAssignToMysqliRector;

use Rector\Php\Rector\Assign\MysqlAssignToMysqliRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MysqlAssignToMysqliRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return MysqlAssignToMysqliRector::class;
    }
}
