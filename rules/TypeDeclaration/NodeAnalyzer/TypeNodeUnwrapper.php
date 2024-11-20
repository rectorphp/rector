<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use Rector\PhpParser\Comparing\NodeComparator;
final class TypeNodeUnwrapper
{
    /**
     * @readonly
     */
    private NodeComparator $nodeComparator;
    public function __construct(NodeComparator $nodeComparator)
    {
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param array<UnionType|NullableType|Name|Identifier|IntersectionType> $typeNodes
     * @return array<Name|Identifier>
     */
    public function unwrapNullableUnionTypes(array $typeNodes) : array
    {
        $unwrappedTypeNodes = [];
        foreach ($typeNodes as $typeNode) {
            if ($typeNode instanceof UnionType) {
                $unwrappedTypeNodes = \array_merge($unwrappedTypeNodes, $this->unwrapNullableUnionTypes($typeNode->types));
            } elseif ($typeNode instanceof NullableType) {
                $unwrappedTypeNodes[] = $typeNode->type;
                $unwrappedTypeNodes[] = new Identifier('null');
            } elseif ($typeNode instanceof IntersectionType) {
                $unwrappedTypeNodes = \array_merge($unwrappedTypeNodes, $this->unwrapNullableUnionTypes($typeNode->types));
            } else {
                $unwrappedTypeNodes[] = $typeNode;
            }
        }
        return $this->uniquateNodes($unwrappedTypeNodes);
    }
    /**
     * @template TNode as Node
     *
     * @param TNode[] $nodes
     * @return TNode[]
     */
    public function uniquateNodes(array $nodes) : array
    {
        $uniqueNodes = [];
        foreach ($nodes as $node) {
            $uniqueHash = $this->nodeComparator->printWithoutComments($node);
            $uniqueNodes[$uniqueHash] = $node;
        }
        // reset keys from 0, for further compatibility
        return \array_values($uniqueNodes);
    }
}
