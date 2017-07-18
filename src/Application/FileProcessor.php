<?php declare(strict_types=1);

namespace Rector\Application;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use Rector\Printer\CodeStyledPrinter;
use SplFileInfo;

final class FileProcessor
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var CodeStyledPrinter
     */
    private $codeStyledPrinter;

    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    public function __construct(Parser $parser, CodeStyledPrinter $codeStyledPrinter, NodeTraverser $nodeTraverser)
    {
        $this->parser = $parser;
        $this->codeStyledPrinter = $codeStyledPrinter;
        $this->nodeTraverser = $nodeTraverser;
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


        $this->nodeTraverser->traverse($nodes);


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
