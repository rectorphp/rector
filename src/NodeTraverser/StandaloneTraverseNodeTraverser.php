<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use Rector\NodeTypeResolver\PHPStanNodeScopeResolver;

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
    private $PHPStanNodeScopeResolver;

    public function __construct(PHPStanNodeScopeResolver $PHPStanNodeScopeResolver)
    {
        $this->PHPStanNodeScopeResolver = $PHPStanNodeScopeResolver;
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
//        $this->PHPStanScopeNodeVisitor->setAnalysedFiles([]);

        // required for PHPStan

        $nodes = $this->PHPStanNodeScopeResolver->processNodes($nodes);

//        dump($nodes);
//        die;

        foreach ($this->nodeTraversers as $nodeTraverser) {
            $nodes = $nodeTraverser->traverse($nodes);
        }

        return $nodes;
    }
}
