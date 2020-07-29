<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\StaticCall\StaticCallToFunctionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\StaticCall\StaticCallToFunctionRector;
use Rector\Generic\Tests\Rector\StaticCall\StaticCallToFunctionRector\Source\SomeOldStaticClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StaticCallToFunctionRectorTest extends AbstractRectorTestCase
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
            StaticCallToFunctionRector::class => [
                StaticCallToFunctionRector::STATIC_CALL_TO_FUNCTION_BY_TYPE => [
                    SomeOldStaticClass::class => [
                        'render' => 'view',
                    ],
                ],
            ],
        ];
    }
}
