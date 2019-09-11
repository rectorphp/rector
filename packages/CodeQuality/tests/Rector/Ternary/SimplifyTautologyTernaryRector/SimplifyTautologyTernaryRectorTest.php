<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Ternary\SimplifyTautologyTernaryRector;

use Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyTautologyTernaryRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return SimplifyTautologyTernaryRector::class;
    }
}
