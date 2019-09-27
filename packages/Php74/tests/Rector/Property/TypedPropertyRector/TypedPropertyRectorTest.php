<?php declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector;

use Iterator;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TypedPropertyRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/property.php.inc'];
        yield [__DIR__ . '/Fixture/skip_invalid_property.php.inc'];
        yield [__DIR__ . '/Fixture/bool_property.php.inc'];
        yield [__DIR__ . '/Fixture/class_property.php.inc'];
        yield [__DIR__ . '/Fixture/nullable_property.php.inc'];
        yield [__DIR__ . '/Fixture/static_property.php.inc'];
        yield [__DIR__ . '/Fixture/default_values_for_nullable_iterables.php.inc'];
        yield [__DIR__ . '/Fixture/default_values.php.inc'];
        yield [__DIR__ . '/Fixture/match_types.php.inc'];
        yield [__DIR__ . '/Fixture/match_types_parent.php.inc'];
        yield [__DIR__ . '/Fixture/static_analysis_based.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return TypedPropertyRector::class;
    }
}
