<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\ArrayArgumentInTestToDataProviderRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPUnit\Rector\Class_\ArrayArgumentInTestToDataProviderRector;
use Rector\PHPUnit\ValueObject\ArrayArgumentToDataProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArrayArgumentInTestToDataProviderRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ArrayArgumentInTestToDataProviderRector::class => [
                ArrayArgumentInTestToDataProviderRector::ARRAY_ARGUMENTS_TO_DATA_PROVIDERS => [
                    new ArrayArgumentToDataProvider(
                        'PHPUnit\Framework\TestCase',
                        'doTestMultiple',
                        'doTestSingle',
                        'variable'
                    ),
                ],
            ],
        ];
    }
}
