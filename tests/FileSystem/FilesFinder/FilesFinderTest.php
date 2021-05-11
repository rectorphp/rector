<?php

declare(strict_types=1);

namespace Rector\Core\Tests\FileSystem\FilesFinder;

use Iterator;
use Rector\Core\FileSystem\FilesFinder;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FilesFinderTest extends AbstractTestCase
{
    private FilesFinder $filesFinder;

    protected function setUp(): void
    {
        $this->boot();
        $this->filesFinder = $this->getService(FilesFinder::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function testSingleSuffix(string $suffix, int $count, string $expectedFileName): void
    {
        $foundFiles = $this->filesFinder->findInDirectoriesAndFiles([__DIR__ . '/Source'], [$suffix]);
        $this->assertCount($count, $foundFiles);

        /** @var SmartFileInfo $foundFile */
        $foundFile = array_pop($foundFiles);
        $this->assertSame($expectedFileName, $foundFile->getBasename());
    }

    /**
     * @return Iterator<array<string|int>>
     */
    public function provideData(): Iterator
    {
        yield ['php', 1, 'SomeFile.php'];
        yield ['yml', 1, 'some_config.yml'];
        yield ['yaml', 1, 'other_config.yaml'];
        yield ['php', 1, 'SomeFile.php'];
    }

    public function testMultipleSuffixes(): void
    {
        $foundFiles = $this->filesFinder->findInDirectoriesAndFiles([__DIR__ . '/Source'], ['yaml', 'yml']);
        $this->assertCount(2, $foundFiles);

        $foundFileNames = [];
        foreach ($foundFiles as $foundFile) {
            $foundFileNames[] = $foundFile->getFilename();
        }

        $expectedFoundFileNames = ['some_config.yml', 'other_config.yaml'];

        sort($foundFileNames);
        sort($expectedFoundFileNames);
        $this->assertSame($expectedFoundFileNames, $foundFileNames);
    }
}
