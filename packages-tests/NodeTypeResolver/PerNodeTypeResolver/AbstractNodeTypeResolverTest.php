<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\Testing\TestingParser\TestingParser;

abstract class AbstractNodeTypeResolverTest extends AbstractTestCase
{
    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var TestingParser
     */
    private $testingParser;

    protected function setUp(): void
    {
        $this->boot();

        $this->betterNodeFinder = $this->getService(BetterNodeFinder::class);
        $this->testingParser = $this->getService(TestingParser::class);
        $this->nodeTypeResolver = $this->getService(NodeTypeResolver::class);
    }

    /**
     * @template T as Node
     * @param class-string<T> $type
     * @return T[]
     */
    protected function getNodesForFileOfType(string $file, string $type): array
    {
        $nodes = $this->testingParser->parseFileToDecoratedNodes($file);
        return $this->betterNodeFinder->findInstanceOf($nodes, $type);
    }
}
