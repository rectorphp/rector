<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PhpSpecToPHPUnitRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/full_blown.php.inc'];
        yield [__DIR__ . '/Fixture/let_create_edge.php.inc'];
        yield [__DIR__ . '/Fixture/mocking.php.inc'];
        yield [__DIR__ . '/Fixture/custom_matcher.php.inc'];
        yield [__DIR__ . '/Fixture/exception.php.inc'];
        yield [__DIR__ . '/Fixture/mock_method_call_arguments.php.inc'];
        yield [__DIR__ . '/Fixture/no_self_type_test.php.inc'];
        yield [__DIR__ . '/Fixture/keep_method.php.inc'];
        yield [__DIR__ . '/Fixture/get_wrapped_object.php.inc'];
        yield [__DIR__ . '/Fixture/mock_properties.php.inc'];
        yield [__DIR__ . '/Fixture/mock_properties_non_local.php.inc'];
        yield [__DIR__ . '/Fixture/setup_private_ctor.php.inc'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}
