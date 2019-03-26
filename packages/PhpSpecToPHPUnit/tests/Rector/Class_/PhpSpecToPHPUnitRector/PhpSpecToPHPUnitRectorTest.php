<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector
 * @covers \Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector
 *
 * @covers \Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector
 *
 * @covers \Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector
 * @covers \Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector
 */
final class PhpSpecToPHPUnitRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/full_blown.php.inc',
            __DIR__ . '/Fixture/let_create_edge.php.inc',
            __DIR__ . '/Fixture/mocking.php.inc',
            __DIR__ . '/Fixture/custom_matcher.php.inc',
            __DIR__ . '/Fixture/exception.php.inc',
            __DIR__ . '/Fixture/mock_method_call_arguments.php.inc',
            __DIR__ . '/Fixture/no_self_type_test.php.inc',
            __DIR__ . '/Fixture/keep_method.php.inc',
            __DIR__ . '/Fixture/get_wrapped_object.php.inc',
            __DIR__ . '/Fixture/mock_properties.php.inc',
            __DIR__ . '/Fixture/mock_properties_non_local.php.inc',
        ]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}
