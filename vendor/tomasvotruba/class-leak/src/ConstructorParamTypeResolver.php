<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\ClassLeak;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use RectorPrefix202609\TomasVotruba\ClassLeak\NodeDecorator\FullyQualifiedNameNodeDecorator;
use RectorPrefix202609\TomasVotruba\ClassLeak\NodeVisitor\ConstructorParamTypeNodeVisitor;
/**
 * @see \TomasVotruba\ClassLeak\Tests\ConstructorParamTypeResolver\ConstructorParamTypeResolverTest
 */
final class ConstructorParamTypeResolver
{
    /**
     * @readonly
     */
    private Parser $parser;
    /**
     * @readonly
     */
    private FullyQualifiedNameNodeDecorator $fullyQualifiedNameNodeDecorator;
    public function __construct(Parser $parser, FullyQualifiedNameNodeDecorator $fullyQualifiedNameNodeDecorator)
    {
        $this->parser = $parser;
        $this->fullyQualifiedNameNodeDecorator = $fullyQualifiedNameNodeDecorator;
    }
    /**
     * @return string[]
     */
    public function resolve(string $filePath): array
    {
        /** @var string $fileContents */
        $fileContents = file_get_contents($filePath);
        $stmts = $this->parser->parse($fileContents);
        if ($stmts === null) {
            return [];
        }
        $this->fullyQualifiedNameNodeDecorator->decorate($stmts);
        $constructorParamTypeNodeVisitor = new ConstructorParamTypeNodeVisitor();
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($constructorParamTypeNodeVisitor);
        $nodeTraverser->traverse($stmts);
        return $constructorParamTypeNodeVisitor->getParamTypeNames();
    }
}
