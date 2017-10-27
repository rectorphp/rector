<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue\Tests;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use Rector\Application\FileProcessor;
use Rector\Contract\Parser\ParserInterface;
use Rector\Printer\FormatPerservingPrinter;
use Rector\Tests\AbstractContainerAwareTestCase;
use SplFileInfo;

final class NodeTraverserQueueTest extends AbstractContainerAwareTestCase
{
    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var SplFileInfo
     */
    private $fileInfo;

    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;

    protected function setUp(): void
    {
        $this->fileProcessor = $this->container->get(FileProcessor::class);

        $this->lexer = $this->container->get(Lexer::class);
        $this->parser = $this->container->get(ParserInterface::class);
        $this->fileInfo = new SplFileInfo(__DIR__ . '/NodeTraverserQueueSource/Before.php.inc');
        $this->formatPerservingPrinter = $this->container->get(FormatPerservingPrinter::class);
    }

    public function testTraverseWithoutAnyChange(): void
    {
        $processedFileContent = $this->fileProcessor->processFileWithRectorsToString($this->fileInfo, []);
        $this->assertStringEqualsFile($this->fileInfo->getRealPath(), $processedFileContent);
    }

    public function testRaw(): void
    {
        $oldStmts = $this->parser->parseFile($this->fileInfo->getRealPath());
        $oldTokens = $this->lexer->getTokens();

        $cloningNodeTraverser = new NodeTraverser;
        $cloningNodeTraverser->addVisitor(new CloningVisitor);

        $newStmts = $cloningNodeTraverser->traverse($oldStmts);

        $processedFileContent = $this->formatPerservingPrinter->printToString($newStmts, $oldStmts, $oldTokens);
        $this->assertStringEqualsFile($this->fileInfo->getRealPath(), $processedFileContent);
    }
}
