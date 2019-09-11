<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector;

use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddArrayDefaultToArrayPropertyRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture_2.php.inc'];
        yield [__DIR__ . '/Fixture/count_on_null.php.inc'];
        yield [__DIR__ . '/Fixture/skip_nullable_array.php.inc'];
        yield [__DIR__ . '/Fixture/skip.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AddArrayDefaultToArrayPropertyRector::class;
    }
}
