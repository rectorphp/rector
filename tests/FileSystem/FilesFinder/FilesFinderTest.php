<?php declare(strict_types=1);

namespace Rector\Tests\FileSystem\FilesFinder;

use Iterator;
use Rector\FileSystem\FilesFinder;
use Rector\Tests\AbstractContainerAwareTestCase;

final class FilesFinderTest extends AbstractContainerAwareTestCase
{
    /**
     * @var FilesFinder
     */
    private $filesFinder;

    protected function setUp(): void
    {
        $this->filesFinder = $this->container->get(FilesFinder::class);
    }

    /**
     * @param string[] $suffixes
     * @dataProvider provideData()
     */
    public function test(array $suffixes, int $count, string $fileName): void
    {
        $foundFiles = $this->filesFinder->findInDirectoriesAndFiles([__DIR__ . '/FilesFinderSource'], $suffixes);
        $this->assertCount($count, $foundFiles);

        $foundFile = array_pop($foundFiles);
        $this->assertSame($fileName, $foundFile->getBasename());
    }

    public function provideData(): Iterator
    {
        yield [['php'], 1, 'SomeFile.php'];
        yield [['yml'], 1, 'some_config.yml'];
        yield [['yaml'], 1, 'other_config.yaml'];
        yield [['php'], 1, 'SomeFile.php'];
        yield [['yaml', 'yml'], 2, 'some_config.yml'];
    }
}
