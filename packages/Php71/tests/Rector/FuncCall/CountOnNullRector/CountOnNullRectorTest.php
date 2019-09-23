<?php declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector;

use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CountOnNullRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/preg_match_array.php.inc'];
        yield [__DIR__ . '/Fixture/local_property.php.inc'];
        yield [__DIR__ . '/Fixture/array_countable_class.php.inc'];
        yield [__DIR__ . '/Fixture/countable_annotated_params.php.inc'];
        yield [__DIR__ . '/Fixture/false_true_class.php.inc'];
        yield [__DIR__ . '/Fixture/on_null.php.inc'];
        yield [__DIR__ . '/Fixture/external_property.php.inc'];
        yield [__DIR__ . '/Fixture/double_same_variable.php.inc'];
        yield [__DIR__ . '/Fixture/property_with_doc.php.inc'];
        yield [__DIR__ . '/Fixture/nullable_array.php.inc'];
        yield [__DIR__ . '/Fixture/skip_countable_local_property.php.inc'];
        yield [__DIR__ . '/Fixture/skip_array_merge.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return CountOnNullRector::class;
    }

    protected function getPhpVersion(): string
    {
        return '7.1';
    }
}
