<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector;

use Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertTrueFalseToSpecificMethodRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/is_readable.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AssertTrueFalseToSpecificMethodRector::class;
    }
}
