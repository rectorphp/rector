<?php declare(strict_types=1);

namespace Rector\Application;

use PhpParser\Parser;
use Rector\Dispatcher\NodeDispatcher;
use Rector\Printer\CodeStyledPrinter;
use SplFileInfo;

final class FileProcessor
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeDispatcher
     */
    private $nodeDispatcher;

    /**
     * @var CodeStyledPrinter
     */
    private $codeStyledPrinter;

    public function __construct(Parser $parser, CodeStyledPrinter $codeStyledPrinter, NodeDispatcher $nodeDispatcher)
    {
        $this->parser = $parser;
        $this->nodeDispatcher = $nodeDispatcher;
        $this->codeStyledPrinter = $codeStyledPrinter;
    }

    /**
     * @var SplFileInfo[]
     */
    public function processFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->processFile($file);
        }
    }

    public function processFile(SplFileInfo $file): void
    {
        $fileContent = file_get_contents($file->getRealPath());
        $nodes = $this->parser->parse($fileContent);
        if ($nodes === null) {
            return;
        }

        $originalNodes = $this->cloneArrayOfObjects($nodes);

        foreach ($nodes as $node) {
            $this->nodeDispatcher->dispatch($node);
        }

        $this->codeStyledPrinter->printToFile($file, $originalNodes, $nodes);
    }

    /**
     * @param object[] $data
     * @return object[]
     */
    private function cloneArrayOfObjects(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = clone $value;
        }

        return $data;
    }
}
