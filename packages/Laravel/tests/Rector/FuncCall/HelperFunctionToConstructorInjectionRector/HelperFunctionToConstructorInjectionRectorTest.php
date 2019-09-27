<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector;

use Iterator;
use Rector\Laravel\Rector\FuncCall\HelperFunctionToConstructorInjectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class HelperFunctionToConstructorInjectionRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/view.php.inc'];
        yield [__DIR__ . '/Fixture/broadcast.php.inc'];
        yield [__DIR__ . '/Fixture/session.php.inc'];
        yield [__DIR__ . '/Fixture/route.php.inc'];
        yield [__DIR__ . '/Fixture/config.php.inc'];
        yield [__DIR__ . '/Fixture/back.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return HelperFunctionToConstructorInjectionRector::class;
    }
}
