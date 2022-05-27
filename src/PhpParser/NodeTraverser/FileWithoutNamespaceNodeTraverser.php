<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class FileWithoutNamespaceNodeTraverser extends NodeTraverser
{
    /**
     * @readonly
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    public function __construct(NodeFinder $nodeFinder)
    {
        $this->nodeFinder = $nodeFinder;
    }
    /**
     * @template TNode as Node
     * @param TNode[] $nodes
     * @return TNode[]|FileWithoutNamespace[]
     */
    public function traverse(array $nodes) : array
    {
        $hasNamespace = (bool) $this->nodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if (!$hasNamespace && $nodes !== []) {
            $fileWithoutNamespace = new FileWithoutNamespace($nodes);
            foreach ($nodes as $node) {
                $node->setAttribute(AttributeKey::PARENT_NODE, $fileWithoutNamespace);
            }
            return [$fileWithoutNamespace];
        }
        return $nodes;
    }
}
