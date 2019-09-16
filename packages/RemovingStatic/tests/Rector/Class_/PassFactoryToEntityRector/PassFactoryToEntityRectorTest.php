<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector;

use Rector\RemovingStatic\Rector\Class_\NewUniqueObjectToEntityFactoryRector;
use Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector;
use Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PassFactoryToEntityRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);

        // test factory content
        $this->assertFileExists($this->getTempPath() . '/AnotherClassFactory.php');
        $this->assertFileEquals(
            __DIR__ . '/Source/ExpectedAnotherClassFactory.php',
            $this->getTempPath() . '/AnotherClassFactory.php'
        );
    }

    /**
     * @dataProvider provideDataForTestMultipleArguments()
     */
    public function testMultipleArguments(string $file): void
    {
        $this->markTestSkipped('Conflicting with previous test() for unknown reason. Works well separately');

        $this->doTestFile($file);

        // test factory content
        $this->assertFileExists($this->getTempPath() . '/AnotherClassWithMoreArgumentsFactory.php');
        $this->assertFileEquals(
            __DIR__ . '/Source/ExpectedAnotherClassWithMoreArgumentsFactory.php',
            $this->getTempPath() . '/AnotherClassWithMoreArgumentsFactory.php'
        );
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    /**
     * @return string[]
     */
    public function provideDataForTestMultipleArguments(): iterable
    {
        yield [__DIR__ . '/Fixture/multiple_args.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        $typesToServices = [TurnMeToService::class];

        return [
            PassFactoryToUniqueObjectRector::class => [
                '$typesToServices' => $typesToServices,
            ],
            NewUniqueObjectToEntityFactoryRector::class => [
                '$typesToServices' => $typesToServices,
            ],
        ];
    }
}
