<?php

declare(strict_types=1);

namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ScopeNestingComparator;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class RenderMethodAnalyzer
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ScopeNestingComparator
     */
    private $scopeNestingComparator;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        ScopeNestingComparator $scopeNestingComparator
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->scopeNestingComparator = $scopeNestingComparator;
    }

    public function hasConditionalTemplateAssigns(ClassMethod $classMethod): bool
    {
        $hasConditionalAssigns = false;

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            $classMethod,
            function (Node $node) use (&$hasConditionalAssigns): ?int {
                if (! $node instanceof Assign) {
                    return null;
                }

                if (! $this->isThisTemplatePropertyFetch($node->var)) {
                    return null;
                }

                if ($this->scopeNestingComparator->isNodeConditionallyScoped($node->var)) {
                    $hasConditionalAssigns = true;
                    return NodeTraverser::STOP_TRAVERSAL;
                }

                return null;
            }
        );

        return $hasConditionalAssigns;
    }

    private function isThisTemplatePropertyFetch(Expr $expr): bool
    {
        if (! $expr instanceof PropertyFetch) {
            return false;
        }

        if (! $expr->var instanceof PropertyFetch) {
            return false;
        }

        $nestedPropertyFetch = $expr->var;
        if (! $this->nodeNameResolver->isName($nestedPropertyFetch->var, 'this')) {
            return false;
        }

        return $this->nodeNameResolver->isName($nestedPropertyFetch->name, 'template');
    }
}
