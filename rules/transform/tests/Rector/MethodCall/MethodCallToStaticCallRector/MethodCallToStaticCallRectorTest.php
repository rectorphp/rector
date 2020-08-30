<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\MethodCall\MethodCallToStaticCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MethodCallToStaticCallRector::class => [
                MethodCallToStaticCallRector::METHOD_CALLS_TO_STATIC_CALLS => [
                    new MethodCallToStaticCall(AnotherDependency::class, 'process', 'StaticCaller', 'anotherMethod'),
                ],
            ],
        ];
    }
}
