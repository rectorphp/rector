<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToStaticCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\FuncCall\FuncCallToStaticCallRector;
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
     * @return mixed[]
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
