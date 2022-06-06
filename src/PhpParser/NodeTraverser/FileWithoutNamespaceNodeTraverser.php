<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class FileWithoutNamespaceNodeTraverser extends \PhpParser\NodeTraverser
{
    /**
     * @readonly
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    public function __construct(\PhpParser\NodeFinder $nodeFinder)
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
        $hasNamespace = (bool) $this->nodeFinder->findFirstInstanceOf($nodes, \PhpParser\Node\Stmt\Namespace_::class);
        if (!$hasNamespace && $nodes !== []) {
            $fileWithoutNamespace = new \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace($nodes);
            foreach ($nodes as $node) {
                $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE, $fileWithoutNamespace);
            }
            return [$fileWithoutNamespace];
        }
        return $nodes;
    }
}
