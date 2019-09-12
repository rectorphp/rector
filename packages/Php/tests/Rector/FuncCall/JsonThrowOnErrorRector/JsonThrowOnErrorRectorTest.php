<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\JsonThrowOnErrorRector;

use Rector\Php\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class JsonThrowOnErrorRectorTest extends AbstractRectorTestCase
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
        return JsonThrowOnErrorRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }
}
