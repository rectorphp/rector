<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Covers part of https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-4.0.md
 */
final class IdentifierRector extends AbstractRector
{
    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer, NodeFactory $nodeFactory)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        $types = ['PhpParser\Node\Const_'];

        if (! $this->propertyFetchAnalyzer->isPropertyFetchOnTypes($node, $types, 'name')) {
            return false;
        }

        return true;
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $propertyFetchNode = $this->nodeFactory->createPropertyFetch(
            $propertyFetchNode->var->name,
            $propertyFetchNode->name->toString()
        );

        return new MethodCall($propertyFetchNode, 'toString');
    }
}
