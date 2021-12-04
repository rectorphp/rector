<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use Rector\Core\PhpParser\Comparing\NodeComparator;
final class TypeNodeUnwrapper
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param array<UnionType|NullableType|Name|Identifier> $typeNodes
     * @return array<Name|Identifier>
     */
    public function unwrapNullableUnionTypes(array $typeNodes) : array
    {
        $unwrappedTypeNodes = [];
        foreach ($typeNodes as $typeNode) {
            if ($typeNode instanceof \PhpParser\Node\UnionType) {
                $unwrappedTypeNodes = \array_merge($unwrappedTypeNodes, $typeNode->types);
            } elseif ($typeNode instanceof \PhpParser\Node\NullableType) {
                $unwrappedTypeNodes[] = $typeNode->type;
                $unwrappedTypeNodes[] = new \PhpParser\Node\Identifier('null');
            } else {
                $unwrappedTypeNodes[] = $typeNode;
            }
        }
        return $this->uniquateNodes($unwrappedTypeNodes);
    }
    /**
     * @param Node[] $nodes
     * @return Node[]
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
