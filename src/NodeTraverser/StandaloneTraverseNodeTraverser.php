<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\CloningVisitor;
use Rector\NodeTypeResolver\NodeVisitor\ClassAndMethodResolver;
use Rector\NodeTypeResolver\NodeVisitor\NamespaceResolver;
use Rector\NodeTypeResolver\PHPStanNodeScopeResolver;
use Rector\PhpParser\NodeVisitor\ParentAndNextNodeAddingNodeVisitor;

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
     * @var PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;

    /**
     * @var CloningVisitor
     */
    private $cloningVisitor;

    /**
     * @var ParentAndNextNodeAddingNodeVisitor
     */
    private $parentAndNextNodeAddingNodeVisitor;

    /**
     * @var ClassAndMethodResolver
     */
    private $classAndMethodResolver;

    /**
     * @var NamespaceResolver
     */
    private $namespaceResolver;

    public function __construct(PHPStanNodeScopeResolver $phpStanNodeScopeResolver)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
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
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NodeVisitor\NameResolver());
        $nodes = $nodeTraverser->traverse($nodes);

        $nodes = $this->phpStanNodeScopeResolver->processNodes($nodes);

        foreach ($this->nodeTraversers as $nodeTraverser) {
            $nodes = $nodeTraverser->traverse($nodes);
        }

        return $nodes;
    }
}
