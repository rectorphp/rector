<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertPropertyExistsRector;

use Rector\PHPUnit\Rector\SpecificMethod\AssertPropertyExistsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertPropertyExistsRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return AssertPropertyExistsRector::class;
    }
}
