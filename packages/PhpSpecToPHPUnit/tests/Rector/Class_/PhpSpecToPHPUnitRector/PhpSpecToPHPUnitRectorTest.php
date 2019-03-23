<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector
 * @covers \Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector
 * @covers \Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector
 * @covers \Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector
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
        ]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}
