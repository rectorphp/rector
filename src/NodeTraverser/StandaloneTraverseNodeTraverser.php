<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeScopeResolver;

/**
 * Oppose to NodeTraverser, that traverse ONE node by ALL NodeVisitors,
 * this traverser traverse ALL nodes by one NodeVisitor, THEN passes them to next NodeVisitor.
 */
final class StandaloneTraverseNodeTraverser
{
    /**
     * @var NodeTraverserInterface[]
     */
    private $nodeTraversers = [];

    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    public function __construct(NodeScopeResolver $nodeScopeResolver)
    {
        $this->nodeScopeResolver = $nodeScopeResolver;
    }

    public function addNodeVisitor(NodeVisitor $nodeVisitor): void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($nodeVisitor);
        $this->nodeTraversers[] = $nodeTraverser;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        foreach ($this->nodeTraversers as $nodeTraverser) {
            $nodes = $nodeTraverser->traverse($nodes);
        }

        return $nodes;
    }
}
