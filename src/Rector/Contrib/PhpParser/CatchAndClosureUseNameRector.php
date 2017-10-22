<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $catchNode->var
 *
 * After:
 * - $catchNode->var->name
 */
final class CatchAndClosureUseNameRector extends AbstractRector
{
    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @param string[][]
     */
    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer, NodeFactory $nodeFactory)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        $types = [
            'PhpParser\Node\Stmt\Catch_',
            'PhpParser\Node\Expr\ClosureUse',
        ];

        return $this->propertyFetchAnalyzer->isTypesAndProperty($node, $types, 'var');
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $propertyFetchNode->var = $this->nodeFactory->createPropertyFetch($propertyFetchNode->var->name, 'var');
        $propertyFetchNode->name = 'name';

        return $propertyFetchNode;
    }
}
