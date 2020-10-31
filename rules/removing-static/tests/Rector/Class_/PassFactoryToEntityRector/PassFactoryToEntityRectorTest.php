<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector;

use Iterator;
use Rector\RemovingStatic\Rector\Class_\NewUniqueObjectToEntityFactoryRector;
use Rector\RemovingStatic\Rector\Class_\PassFactoryToUniqueObjectRector;
use Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source\TurnMeToService;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PassFactoryToEntityRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);

        $expectedFactoryFilePath = StaticFixtureSplitter::getTemporaryPath() . '/AnotherClassWithMoreArgumentsFactory.php';

        $this->assertFileExists($expectedFactoryFilePath);
        $this->assertFileEquals(
            __DIR__ . '/Source/ExpectedAnotherClassWithMoreArgumentsFactory.php',
            $expectedFactoryFilePath
        );
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureWithMultipleArguments');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        $typesToServices = [TurnMeToService::class];

        return [
            PassFactoryToUniqueObjectRector::class => [
                PassFactoryToUniqueObjectRector::TYPES_TO_SERVICES => $typesToServices,
            ],
            NewUniqueObjectToEntityFactoryRector::class => [
                NewUniqueObjectToEntityFactoryRector::TYPES_TO_SERVICES => $typesToServices,
            ],
        ];
    }
}
