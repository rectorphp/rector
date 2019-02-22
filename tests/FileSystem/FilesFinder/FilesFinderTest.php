<?php declare(strict_types=1);

namespace Rector\Tests\FileSystem\FilesFinder;

use Iterator;
use Rector\FileSystem\FilesFinder;
use Rector\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class FilesFinderTest extends AbstractKernelTestCase
{
    /**
     * @var FilesFinder
     */
    private $filesFinder;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->filesFinder = self::$container->get(FilesFinder::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function testSingleSuffix(string $suffix, int $count, string $expectedFileName): void
    {
        $foundFiles = $this->filesFinder->findInDirectoriesAndFiles([__DIR__ . '/FilesFinderSource'], [$suffix]);
        $this->assertCount($count, $foundFiles);

        /** @var SmartFileInfo $foundFile */
        $foundFile = array_pop($foundFiles);
        $this->assertSame($expectedFileName, $foundFile->getBasename());
    }

    public function provideData(): Iterator
    {
        yield ['php', 1, 'SomeFile.php'];
        yield ['yml', 1, 'some_config.yml'];
        yield ['yaml', 1, 'other_config.yaml'];
        yield ['php', 1, 'SomeFile.php'];
    }

    public function testMultipleSuffixes(): void
    {
        $foundFiles = $this->filesFinder->findInDirectoriesAndFiles([__DIR__ . '/FilesFinderSource'], ['yaml', 'yml']);
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
