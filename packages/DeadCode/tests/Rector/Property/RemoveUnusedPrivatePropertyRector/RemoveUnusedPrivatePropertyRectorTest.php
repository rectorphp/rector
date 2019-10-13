<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector;

use Iterator;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedPrivatePropertyRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/property_assign.php.inc'];
        yield [__DIR__ . '/Fixture/with_trait.php.inc'];
        yield [__DIR__ . '/Fixture/skip_magically_accessed.php.inc'];
        yield [__DIR__ . '/Fixture/skip_magically_accessed_fetch.php.inc'];
        yield [__DIR__ . '/Fixture/skip_doctrine_entity_property.php.inc'];
        yield [__DIR__ . '/Fixture/skip_anonymous_class.php.inc'];
        yield [__DIR__ . '/Fixture/skip_anonymous_function.php.inc'];
        yield [__DIR__ . '/Fixture/skip_nested_closure.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedPrivatePropertyRector::class;
    }
}
