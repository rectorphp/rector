<?php

declare(strict_types=1);

namespace Rector\Testing\PHPUnit\Runnable;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Testing\Contract\RunnableInterface;

final class RunnableClassFinder
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    public function __construct(NodeFinder $nodeFinder)
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->nodeFinder = $nodeFinder;
    }

    public function find(string $content): string
    {
        /** @var Node[] $nodes */
        $nodes = $this->parser->parse($content);
        $this->decorateNodesWithNames($nodes);

        $class = $this->findClassThatImplementsRunnableInterface($nodes);

        return (string) $class->namespacedName;
    }

    /**
     * @param Node[] $nodes
     */
    private function decorateNodesWithNames(array $nodes): void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver(null, [
            'preserveOriginalNames' => true,
        ]));
        $nodeTraverser->traverse($nodes);
    }

    /**
     * @param Node[] $nodes
     */
    private function findClassThatImplementsRunnableInterface(array $nodes): Class_
    {
        $class = $this->nodeFinder->findFirst($nodes, function (Node $node): bool {
            if (! $node instanceof Class_) {
                return false;
            }

            foreach ($node->implements as $implement) {
                if ((string) $implement !== RunnableInterface::class) {
                    continue;
                }

                return true;
            }

            return false;
        });

        if (! $class instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        return $class;
    }
}
