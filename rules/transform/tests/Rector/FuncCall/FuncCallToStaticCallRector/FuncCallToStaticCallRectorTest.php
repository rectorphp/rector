<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToStaticCallRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FuncCallToStaticCallRectorTest extends AbstractRectorTestCase
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
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            FuncCallToStaticCallRector::class => [
                FuncCallToStaticCallRector::FUNC_CALLS_TO_STATIC_CALLS => [
                    new FuncCallToStaticCall('view', 'SomeStaticClass', 'render'),
                    new FuncCallToStaticCall('SomeNamespaced\view', 'AnotherStaticClass', 'render'),
                ],
            ],
        ];
    }
}
