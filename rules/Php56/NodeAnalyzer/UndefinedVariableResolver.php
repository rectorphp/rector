<?php

declare(strict_types=1);

namespace Rector\Php56\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Cast\Unset_ as UnsetCast;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class UndefinedVariableResolver
{
    public function __construct(
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private NodeNameResolver $nodeNameResolver,
    ) {
    }

    /**
     * @return string[]
     */
    public function resolve(ClassMethod | Function_ | Closure $node): array
    {
        $undefinedVariables = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $node->stmts, function (Node $node) use (
            &$undefinedVariables
        ): ?int {
            // entering new scope - break!
            if ($node instanceof FunctionLike && ! $node instanceof ArrowFunction) {
                return NodeTraverser::STOP_TRAVERSAL;
            }

            if ($node instanceof Foreach_) {
                // handled above
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }

            if (! $node instanceof Variable) {
                return null;
            }

            if ($this->shouldSkipVariable($node)) {
                return null;
            }

            /** @var string $variableName */
            $variableName = $this->nodeNameResolver->getName($node);

            // defined 100 %
            /** @var Scope $scope */
            $scope = $node->getAttribute(AttributeKey::SCOPE);
            if ($scope->hasVariableType($variableName)->yes()) {
                return null;
            }

            $undefinedVariables[] = $variableName;

            return null;
        });

        return array_unique($undefinedVariables);
    }

    private function shouldSkipVariable(Variable $variable): bool
    {
        $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Node) {
            return true;
        }

        if ($parentNode instanceof Global_) {
            return true;
        }

        if ($parentNode instanceof Node &&
            (
                $parentNode instanceof Assign || $parentNode instanceof AssignRef || $this->isStaticVariable(
                    $parentNode
                )
            )) {
            return true;
        }

        if ($parentNode instanceof Unset_ || $parentNode instanceof UnsetCast) {
            return true;
        }

        // list() = | [$values] = defines variables as null
        if ($this->isListAssign($parentNode)) {
            return true;
        }

        $nodeScope = $variable->getAttribute(AttributeKey::SCOPE);
        if (! $nodeScope instanceof Scope) {
            return true;
        }

        $variableName = $this->nodeNameResolver->getName($variable);

        // skip $this, as probably in outer scope
        if ($variableName === 'this') {
            return true;
        }

        return $variableName === null;
    }

    private function isStaticVariable(Node $parentNode): bool
    {
        // definition of static variable
        if ($parentNode instanceof StaticVar) {
            $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentParentNode instanceof Static_) {
                return true;
            }
        }

        return false;
    }

    private function isListAssign(Node $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof List_) {
            return true;
        }

        return $parentNode instanceof Array_;
    }
}
