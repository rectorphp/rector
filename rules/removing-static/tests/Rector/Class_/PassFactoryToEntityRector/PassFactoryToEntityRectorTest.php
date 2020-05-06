<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\RemovingStatic\Rector\Class_\NewUniqueObjectToEntityFactoryRector;
use Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector;
use Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService;

final class PassFactoryToEntityRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);

        // test factory content
        $this->assertFileExists($this->getTempPath() . '/AnotherClassWithMoreArgumentsFactory.php');
        $this->assertFileEquals(
            __DIR__ . '/Source/ExpectedAnotherClassWithMoreArgumentsFactory.php',
            $this->getTempPath() . '/AnotherClassWithMoreArgumentsFactory.php'
        );
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureWithMultipleArguments');
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
