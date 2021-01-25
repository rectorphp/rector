<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\AlreadyAssignDetector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeTraverser;
use Rector\NodeNestingScope\ScopeNestingComparator;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;

/**
 * Should add extra null type
 */
final class NullTypeAssignDetector extends AbstractAssignDetector
{
    /**
     * @var ScopeNestingComparator
     */
    private $scopeNestingComparator;

    /**
     * @var DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        ScopeNestingComparator $scopeNestingComparator,
        DoctrineTypeAnalyzer $doctrineTypeAnalyzer,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->scopeNestingComparator = $scopeNestingComparator;
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function detect(ClassLike $classLike, string $propertyName): ?bool
    {
        $needsNullType = null;

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classLike->stmts, function (Node $node) use (
            $propertyName, &$needsNullType
        ): ?int {
            $expr = $this->matchAssignExprToPropertyName($node, $propertyName);
            if (! $expr instanceof Expr) {
                return null;
            }

            if ($this->scopeNestingComparator->isNodeConditionallyScoped($expr)) {
                $needsNullType = true;
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }

            // not in doctrine property
            $staticType = $this->nodeTypeResolver->getStaticType($expr);
            if ($this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($staticType)) {
                $needsNullType = false;
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }

            return null;
        });

        return $needsNullType;
    }
}
