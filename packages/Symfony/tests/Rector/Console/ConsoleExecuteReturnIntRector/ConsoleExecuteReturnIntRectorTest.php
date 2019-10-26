<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExecuteReturnIntRector;

use Iterator;
use Rector\Symfony\Rector\Console\ConsoleExecuteReturnIntRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConsoleExecuteReturnIntRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/extends-command-not-directly.php.inc'];
        yield [__DIR__ . '/Fixture/explicit-return-null.php.inc'];
        yield [__DIR__ . '/Fixture/no-return.php.inc'];
        yield [__DIR__ . '/Fixture/empty-return.php.inc'];
        yield [__DIR__ . '/Fixture/multiple-returns.php.inc'];
        yield [__DIR__ . '/Fixture/add-return-type.php.inc'];
        yield [__DIR__ . '/Fixture/return-function-call.php.inc'];
        yield [__DIR__ . '/Fixture/return-static-function-call.php.inc'];

        // skip
        yield [__DIR__ . '/Fixture/skip_non_console_command.php.inc'];
        yield [__DIR__ . '/Fixture/skip_already_int.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ConsoleExecuteReturnIntRector::class;
    }
}
