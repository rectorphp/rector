<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Parser;

use RectorPrefix202211\Nette\Utils\FileSystem;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;
final class SimplePhpParser
{
    /**
     * @readonly
     * @var \PhpParser\Parser
     */
    private $phpParser;
    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->phpParser = $parserFactory->create(ParserFactory::PREFER_PHP7);
    }
    /**
     * @return Stmt[]
     */
    public function parseFile(string $filePath) : array
    {
        $fileContent = FileSystem::read($filePath);
        return $this->parseString($fileContent);
    }
    /**
     * @return Stmt[]
     */
    public function parseString(string $fileContent) : array
    {
        $stmts = $this->phpParser->parse($fileContent);
        if ($stmts === null) {
            return [];
        }
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NodeConnectingVisitor());
        return $nodeTraverser->traverse($stmts);
    }
}
