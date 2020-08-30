<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToFuncCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\Tests\Rector\StaticCall\StaticCallToFuncCallRector\Source\SomeOldStaticClass;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StaticCallToFuncCallRectorTest extends AbstractRectorTestCase
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
            StaticCallToFuncCallRector::class => [
                StaticCallToFuncCallRector::STATIC_CALLS_TO_FUNCTIONS => [
                    new StaticCallToFuncCall(SomeOldStaticClass::class, 'render', 'view'),
                ],
            ],
        ];
    }
}
