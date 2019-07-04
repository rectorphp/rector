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
        $this->assertFileExists($this->getTempPath() . '/AnotherClassFactory.php');
        $this->assertFileEquals(
            __DIR__ . '/Source/ExpectedAnotherClassFactory.php',
            $this->getTempPath() . '/AnotherClassFactory.php'
        );
    }

    public function testMultipleArguments(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/multiple_args.php.inc']);

        // test factory content
        $this->assertFileExists($this->getTempPath() . '/AnotherClassWithMoreArgumentsFactory.php');
        $this->assertFileEquals(
            __DIR__ . '/Source/ExpectedAnotherClassWithMoreArgumentsFactory.php',
            $this->getTempPath() . '/AnotherClassWithMoreArgumentsFactory.php'
        );
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/custom-config.yaml';
    }
}
