<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Dynamic\NamespaceReplacerRector;

use PHPUnit\Framework\TestCase;
use Rector\Application\FileProcessor;
use Rector\DependencyInjection\ContainerFactory;
use Rector\Rector\Dynamic\NamespaceReplacerRector;
use SplFileInfo;

final class Test extends TestCase
{
    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    protected function setUp(): void
    {
        $container = (new ContainerFactory)->createWithConfig(
            __DIR__ . '/config/rector.yml'
        );

        $this->fileProcessor = $container->get(FileProcessor::class);
    }

    /**
     * @dataProvider provideTestFiles()
     */
    public function test(string $testedFile, string $expectedFile): void
    {
        $refactoredFileContent = $this->fileProcessor->processFileWithRectorsToString(
            new SplFileInfo($testedFile),
            [NamespaceReplacerRector::class]
        );

        $this->assertStringEqualsFile($expectedFile, $refactoredFileContent);
    }

    /**
     * @return string[][]
     */
    public function provideTestFiles(): array
    {
        return [
            [__DIR__ . '/wrong/wrong.php.inc', __DIR__ . '/correct/correct.php.inc'],
        ];
    }
}
