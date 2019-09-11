<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentPregDelimiterRector;

use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConsistentPregDelimiterRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/escape_nette_static_call.php.inc'];
        yield [__DIR__ . '/Fixture/skip_concat.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ConsistentPregDelimiterRector::class;
    }
}
