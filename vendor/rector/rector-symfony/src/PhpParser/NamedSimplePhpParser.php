<?php

declare (strict_types=1);
namespace Rector\Symfony\PhpParser;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
final class NamedSimplePhpParser
{
    /**
     * @readonly
     * @var \PhpParser\Parser
     */
    private $phpParser;
    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->phpParser = $parserFactory->create(ParserFactory::ONLY_PHP7);
    }
    /**
     * @return Stmt[]
     */
    public function parseString(string $content) : array
    {
        $stmts = $this->phpParser->parse($content);
        if ($stmts === null) {
            return [];
        }
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->traverse($stmts);
        return $stmts;
    }
}
