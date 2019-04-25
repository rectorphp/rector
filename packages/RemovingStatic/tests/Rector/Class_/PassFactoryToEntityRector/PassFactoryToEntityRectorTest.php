<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\RemovingStatic\PassFactoryUniqueObjectRector
 * @covers \Rector\RemovingStatic\NewUniqueObjectToEntityFactoryRector
 */
final class PassFactoryToEntityRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);

        // test factory content
        $this->assertFileExists('/tmp/rector_temp_tests/AnotherClassFactory.php');
        $this->assertFileEquals(
            __DIR__ . '/Source/ExpectedAnotherClassFactory.php',
            '/tmp/rector_temp_tests/AnotherClassFactory.php'
        );
    }

    public function testMultipleArguments(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/multiple_args.php.inc']);

        // test factory content
        $this->assertFileExists('/tmp/rector_temp_tests/AnotherClassWithMoreArgumentsFactory.php');
        $this->assertFileEquals(
            __DIR__ . '/Source/ExpectedAnotherClassWithMoreArgumentsFactory.php',
            '/tmp/rector_temp_tests/AnotherClassWithMoreArgumentsFactory.php'
        );
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/custom-config.yaml';
    }
}
