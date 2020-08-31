<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector;

use InvalidArgumentException;
use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InvalidConfigurationTest extends AbstractRectorTestCase
{
    /**
     * @var string[][]
     */
    private const CONFIGURATION = [
        ['Nette\Utils\FileSystem', 'write', 'Symplify\SmartFileSystem\SmartFileSystem', 'dumpFile'],
    ];

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

    /**
     * @return array<string, array<string[][]>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            StaticCallToMethodCallRector::class => [
                StaticCallToMethodCallRector::STATIC_CALLS_TO_METHOD_CALLS => self::CONFIGURATION,
            ],
        ];
    }
}
