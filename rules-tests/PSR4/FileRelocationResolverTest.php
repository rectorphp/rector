<?php

declare(strict_types=1);

namespace Rector\Tests\PSR4;

use Iterator;
use Rector\PSR4\FileRelocationResolver;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\Tests\PSR4\Source\SomeFile;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileRelocationResolverTest extends AbstractTestCase
{
    private FileRelocationResolver $fileRelocationResolver;

    protected function setUp(): void
    {
        $this->boot();

        $this->fileRelocationResolver = $this->getService(FileRelocationResolver::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(string $file, string $oldClass, string $newClass, string $expectedNewFileLocation): void
    {
        $smartFileInfo = new SmartFileInfo($file);

        $newFileLocation = $this->fileRelocationResolver->resolveNewFileLocationFromOldClassToNewClass(
            $smartFileInfo,
            $oldClass,
            $newClass
        );

        $this->assertSame($expectedNewFileLocation, $newFileLocation);
    }

    /**
     * @return Iterator<mixed>
     */
    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/Source/SomeFile.php',
            SomeFile::class,
            'Rector\Tests\PSR10\Source\SomeFile',
            'rules-tests/PSR10/Source/SomeFile.php',
        ];
    }
}
