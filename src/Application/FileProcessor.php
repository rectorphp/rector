<?php declare(strict_types=1);

namespace Rector\Application;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use Rector\Printer\FormatPerservingPrinter;
use SplFileInfo;

final class FileProcessor
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var FormatPerservingPrinter
     */
    private $codeStyledPrinter;

    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    /**
     * @var Lexer
     */
    private $lexer;

    public function __construct(Parser $parser, FormatPerservingPrinter $codeStyledPrinter, Lexer $lexer, NodeTraverser $nodeTraverser)
    {
        $this->parser = $parser;
        $this->codeStyledPrinter = $codeStyledPrinter;
        $this->nodeTraverser = $nodeTraverser;
        $this->lexer = $lexer;
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
        $oldStmts = $this->parser->parse($fileContent);
        if ($oldStmts === null) {
            return;
        }

        $oldStmts = $this->cloneArrayOfObjects($oldStmts);

        $oldTokens = $this->lexer->getTokens();

        $newStmts = $this->nodeTraverser->traverse($oldStmts);

        $this->codeStyledPrinter->printToFile($file, $newStmts, $oldStmts, $oldTokens);
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
