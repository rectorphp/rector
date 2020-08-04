<?php

declare(strict_types=1);

namespace Rector\Injection\Tests\Rector\StaticCall\StaticCallToMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Injection\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Injection\ValueObject\StaticCallToMethodCall;
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

    protected function getRectorsWithConfiguration(): array
    {
        $configuration = [
            new StaticCallToMethodCall(
                'Nette\Utils\FileSystem',
                'write',
                'Symplify\SmartFileSystem\SmartFileSystem',
                'dumpFile'
            ),
        ];

        return [
            StaticCallToMethodCallRector::class => [
                StaticCallToMethodCallRector::STATIC_CALLS_TO_METHOD_CALLS => $configuration,
            ],
        ];
    }
}
