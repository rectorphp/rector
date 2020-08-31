<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StaticCallToMethodCallRectorTest extends AbstractRectorTestCase
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
            StaticCallToMethodCallRector::class => [
                StaticCallToMethodCallRector::STATIC_CALLS_TO_METHOD_CALLS => [
                    new StaticCallToMethodCall(
                        'Nette\Utils\FileSystem',
                        'write',
                        'Symplify\SmartFileSystem\SmartFileSystem',
                        'dumpFile'
                    ),
                    new StaticCallToMethodCall(
                        'Illuminate\Support\Facades\Response',
                        '*',
                        'Illuminate\Contracts\Routing\ResponseFactory',
                        '*'
                    ),
                ],
            ],
        ];
    }
}
