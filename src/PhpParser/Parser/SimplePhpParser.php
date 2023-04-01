<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Parser;

use RectorPrefix202304\Nette\Utils\FileSystem;
use PhpParser\Node\Stmt;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Rector\Core\PhpParser\NodeTraverser\NodeConnectingTraverser;
final class SimplePhpParser
{
    /**
     * @readonly
     * @var \PhpParser\Parser
     */
    private $phpParser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeTraverser\NodeConnectingTraverser
     */
    private $nodeConnectingTraverser;
    public function __construct(NodeConnectingTraverser $nodeConnectingTraverser)
    {
        $this->nodeConnectingTraverser = $nodeConnectingTraverser;
        $parserFactory = new ParserFactory();
        $this->phpParser = $parserFactory->create(ParserFactory::PREFER_PHP7);
    }
    /**
     * @api tests
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
        return $this->nodeConnectingTraverser->traverse($stmts);
    }
}
