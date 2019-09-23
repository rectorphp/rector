<?php declare(strict_types=1);

namespace Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector;

use Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PregReplaceEModifierRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/call_function.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return PregReplaceEModifierRector::class;
    }
}
