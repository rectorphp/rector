<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Dynamic\MethodArgumentChangerRector;

use PHPUnit\Framework\TestCase;
use Rector\Application\FileProcessor;
use Rector\DependencyInjection\ContainerFactory;
use Rector\Rector\Dynamic\MethodArgumentChangerRector;
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
    public function testProcessing(string $testedFile, string $expectedFile): void
    {
        $refactoredFileContent = $this->fileProcessor->processFileWithRectorsToString(
            new SplFileInfo($testedFile),
            [MethodArgumentChangerRector::class]
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
            [__DIR__ . '/wrong/wrong2.php.inc', __DIR__ . '/correct/correct2.php.inc'],
        ];
    }
}
