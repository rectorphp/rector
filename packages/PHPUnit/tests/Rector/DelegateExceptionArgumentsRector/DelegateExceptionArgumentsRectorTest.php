<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\DelegateExceptionArgumentsRector;

use Rector\PHPUnit\Rector\DelegateExceptionArgumentsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class DelegateExceptionArgumentsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function getRectorClass(): string
    {
        return DelegateExceptionArgumentsRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/message.php.inc'];
        yield [__DIR__ . '/Fixture/regexp.php.inc'];
        yield [__DIR__ . '/Fixture/self_nested.php.inc'];
    }
}
