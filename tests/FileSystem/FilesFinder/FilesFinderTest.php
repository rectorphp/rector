<?php

declare(strict_types=1);

namespace Rector\Core\Tests\FileSystem\FilesFinder;

use Iterator;
use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class FilesFinderTest extends AbstractKernelTestCase
{
    /**
     * @var FilesFinder
     */
    private $filesFinder;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->filesFinder = $this->getService(FilesFinder::class);
        $this->smartFileSystem = $this->getService(SmartFileSystem::class);
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

    public function testMatchGitDiff(): void
    {
        $dir = sys_get_temp_dir() . '/' . mt_rand();
        mkdir($dir);
        chdir($dir);
        shell_exec('git init');

        $filename = $dir . '/tmp.php';
        touch($filename);
        touch($dir . '/tmp.yml');

        shell_exec('git add --all && git commit -m "first commit"');

        $this->smartFileSystem->dumpFile($filename, '<?php echo ' . mt_rand() . ';');
        $this->smartFileSystem->dumpFile($dir . '/tmp.yml', '');

        $foundFiles = $this->filesFinder->findInDirectoriesAndFiles([$dir], ['php'], true);
        $this->assertCount(1, $foundFiles);

        $this->smartFileSystem->remove($filename);
    }
}
