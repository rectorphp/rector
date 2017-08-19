<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\NodeTraverser\TypeDetectingNodeTraverser;

final class NodeTypeResolver
{
    /**
     * @var TypeDetectingNodeTraverser
     */
    private $typeDetectingNodeTraverser;

    public function __construct(TypeDetectingNodeTraverser $typeDetectingNodeTraverser)
    {
        $this->typeDetectingNodeTraverser = $typeDetectingNodeTraverser;
    }

    /**
     * @param Node[] $nodes
     */
    public function getTypeForNode(Node $activeNode, array $nodes): void
    {
        $this->typeDetectingNodeTraverser->traverse($nodes);
    }
}
