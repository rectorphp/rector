<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class FileWithoutNamespaceNodeTraverser extends NodeTraverser
{
    /**
     * @template TNode as Node
     * @param TNode[] $nodes
     * @return TNode[]|FileWithoutNamespace[]
     */
    public function traverse(array $nodes) : array
    {
        foreach ($nodes as $node) {
            if ($node instanceof Namespace_) {
                return $nodes;
            }
        }
        $fileWithoutNamespace = new FileWithoutNamespace($nodes);
        return [$fileWithoutNamespace];
    }
}
