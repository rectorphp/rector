<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Dynamic;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Rector\Application\FileProcessor;
use Rector\DependencyInjection\ContainerFactory;
use Rector\Rector\Dynamic\ClassReplacerRector;
use SplFileInfo;

final class Test extends TestCase
{
    /**
     * @var ClassReplacerRector
     */
    private $classReplacerRector;

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
        $this->classReplacerRector = $container->get(ClassReplacerRector::class);
    }

    public function testConfiguration(): void
    {
        $oldToNewClasses = Assert::getObjectAttribute($this->classReplacerRector,'oldToNewClasses');
        $this->assertNotSame([], $oldToNewClasses);
    }

    public function testProcessing(): void
    {
        $refactoredFileContent = $this->fileProcessor->processFileWithRectorsToString(
            new SplFileInfo(__DIR__ . '/wrong/wrong.php.inc'),
            [ClassReplacerRector::class]
        );

        $this->assertStringEqualsFile(__DIR__ . '/correct/correct.php.inc', $refactoredFileContent);
    }
}
