<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\String_\ManualJsonStringToJsonEncodeArrayRector;

use Rector\CodingStyle\Rector\String_\ManualJsonStringToJsonEncodeArrayRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ManualJsonStringToJsonEncodeArrayRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/concat_json.php.inc'];
        yield [__DIR__ . '/Fixture/multiline_concat_json.php.inc'];
        yield [__DIR__ . '/Fixture/tripleline_multiline_concat_json.php.inc'];
        yield [__DIR__ . '/Fixture/assign_with_concat.php.inc'];
        yield [__DIR__ . '/Fixture/with_implode.php.inc'];
        yield [__DIR__ . '/Fixture/without_assign.php.inc'];
        yield [__DIR__ . '/Fixture/array_concat.php.inc'];
        yield [__DIR__ . '/Fixture/simple_row_with_spaces.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ManualJsonStringToJsonEncodeArrayRector::class;
    }
}
