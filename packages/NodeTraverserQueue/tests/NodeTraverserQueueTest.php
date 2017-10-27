<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue\Tests;

use Rector\Application\FileProcessor;
use Rector\Tests\AbstractContainerAwareTestCase;
use SplFileInfo;

final class NodeTraverserQueueTest extends AbstractContainerAwareTestCase
{
    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    protected function setUp(): void
    {
        $this->fileProcessor = $this->container->get(FileProcessor::class);
    }

    public function testTraverseWithoutAnyChange(): void
    {
        $fileInfo = new SplFileInfo(__DIR__ . '/NodeTraverserQueueSource/Before.php.inc');

        $processedFileContent = $this->fileProcessor->processFileWithRectorsToString($fileInfo, []);

        $this->assertStringEqualsFile(
            $processedFileContent,
            __DIR__ . '/NodeTraverserQueueSource/Before.php.inc'
        );
    }
}
