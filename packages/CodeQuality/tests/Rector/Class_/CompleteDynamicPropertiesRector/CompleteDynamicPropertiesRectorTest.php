<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector;

use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CompleteDynamicPropertiesRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/multiple_types.php.inc'];
        yield [__DIR__ . '/Fixture/skip_defined.php.inc'];
        yield [__DIR__ . '/Fixture/skip_parent_property.php.inc'];
        yield [__DIR__ . '/Fixture/skip_trait_used.php.inc'];
        yield [__DIR__ . '/Fixture/skip_magic_parent.php.inc'];
        yield [__DIR__ . '/Fixture/skip_magic.php.inc'];
        yield [__DIR__ . '/Fixture/skip_laravel_closure_binding.php.inc'];
        yield [__DIR__ . '/Fixture/skip_dynamic_properties.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return CompleteDynamicPropertiesRector::class;
    }
}
