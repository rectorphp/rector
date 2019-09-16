<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Ternary\TernaryToSpaceshipRector;

use Rector\Php\Rector\Ternary\TernaryToSpaceshipRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TernaryToSpaceshipRectorTest extends AbstractRectorTestCase
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
        return TernaryToSpaceshipRector::class;
    }
}
