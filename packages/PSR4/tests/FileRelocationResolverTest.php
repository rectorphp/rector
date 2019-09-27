<?php declare(strict_types=1);

namespace Rector\PSR4\Tests;

use Iterator;
use Rector\HttpKernel\RectorKernel;
use Rector\PSR4\FileRelocationResolver;
use Rector\PSR4\Tests\Source\SomeFile;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class FileRelocationResolverTest extends AbstractKernelTestCase
{
    /**
     * @var FileRelocationResolver
     */
    private $fileRelocationResolver;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->fileRelocationResolver = self::$container->get(FileRelocationResolver::class);
    }

    /**
     * @dataProvider provideDataForTest()
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

    public function provideDataForTest(): Iterator
    {
        yield [
            __DIR__ . '/Source/SomeFile.php',
            SomeFile::class,
            'Rector\PSR10\Tests\Source\SomeFile',
            'packages/PSR10/tests/Source/SomeFile.php',
        ];
    }
}
