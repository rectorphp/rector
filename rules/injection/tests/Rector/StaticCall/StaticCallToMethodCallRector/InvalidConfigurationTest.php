<?php

declare(strict_types=1);

namespace Rector\Injection\Tests\Rector\StaticCall\StaticCallToMethodCallRector;

use InvalidArgumentException;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Injection\Rector\StaticCall\StaticCallToMethodCallRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InvalidConfigurationTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorsWithConfiguration(): array
    {
        $configuration = [
            ['Nette\Utils\FileSystem', 'write', 'Symplify\SmartFileSystem\SmartFileSystem', 'dumpFile'],
        ];

        return [
            StaticCallToMethodCallRector::class => [
                StaticCallToMethodCallRector::STATIC_CALLS_TO_METHOD_CALLS => $configuration,
            ],
        ];
    }
}
