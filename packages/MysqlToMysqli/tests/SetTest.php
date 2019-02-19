<?php declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SetTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/SetFixture.php.inc']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/../../../config/level/database-migration/mysql-to-mysqli.yaml';
    }
}
