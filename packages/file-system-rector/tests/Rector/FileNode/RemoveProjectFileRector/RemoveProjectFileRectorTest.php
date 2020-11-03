<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Tests\Rector\FileNode\RemoveProjectFileRector;

use Iterator;
use Rector\FileSystemRector\Rector\FileNode\RemoveProjectFileRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveProjectFileRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fixtureFileInfo): void
    {
        $this->doTestFileInfo($fixtureFileInfo);
        $this->assertFileWasRemoved($this->originalTempFileInfo);
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
            RemoveProjectFileRector::class => [
                RemoveProjectFileRector::FILE_PATHS_TO_REMOVE => ['file_to_be_removed.php'],
            ],
        ];
    }
}
