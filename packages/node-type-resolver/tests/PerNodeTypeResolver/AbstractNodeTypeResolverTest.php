<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver;

use PhpParser\Node;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Testing\TestingParser\TestingParser;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

abstract class AbstractNodeTypeResolverTest extends AbstractKernelTestCase
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
        $this->bootKernel(RectorKernel::class);

        $this->betterNodeFinder = self::$container->get(BetterNodeFinder::class);
        $this->testingParser = self::$container->get(TestingParser::class);
        $this->nodeTypeResolver = self::$container->get(NodeTypeResolver::class);
    }

    /**
     * @return Node[]
     */
    protected function getNodesForFileOfType(string $file, string $type): array
    {
        $nodes = $this->testingParser->parseFileToDecoratedNodes($file);

        return $this->betterNodeFinder->findInstanceOf($nodes, $type);
    }
}
