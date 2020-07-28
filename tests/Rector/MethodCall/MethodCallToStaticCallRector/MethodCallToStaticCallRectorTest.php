<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MethodCall\MethodCallToStaticCallRector;

use Iterator;
use Rector\Core\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MethodCallToStaticCallRectorTest extends AbstractRectorTestCase
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

    protected function getRectorsWithConfiguration(): array
    {
        return [
            MethodCallToStaticCallRector::class => [
                '$methodCallsToStaticCalls' => [
                    'AnotherDependency' => [['StaticCaller', 'anotherMethod']],
                ],
            ],
        ];
    }
}
