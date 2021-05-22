<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class SingleMethodAssignedNodePropertyTypeInferer implements PropertyTypeInfererInterface
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private NodeTypeResolver $nodeTypeResolver,
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function inferProperty(Property $property): ?Type
    {
        $classLike = $this->betterNodeFinder->findParentType($property, ClassLike::class);
        if (! $classLike instanceof Class_) {
            return null;
        }

        $classMethod = $classLike->getMethod(MethodName::CONSTRUCT);
        if (! $classMethod instanceof ClassMethod) {
            return null;
        }

        $propertyName = $this->nodeNameResolver->getName($property);
        $assignedNode = $this->resolveAssignedNodeToProperty($classMethod, $propertyName);
        if (! $assignedNode instanceof Expr) {
            return null;
        }

        return $this->nodeTypeResolver->getStaticType($assignedNode);
    }

    public function getPriority(): int
    {
        return 750;
    }

    private function resolveAssignedNodeToProperty(ClassMethod $classMethod, string $propertyName): ?Expr
    {
        $assignedNode = null;

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node) use ($propertyName, &$assignedNode): ?int {
                if (! $node instanceof Assign) {
                    return null;
                }

                if (! $this->nodeNameResolver->isName($node->var, $propertyName)) {
                    return null;
                }

                $assignedNode = $node->expr;

                return NodeTraverser::STOP_TRAVERSAL;
            }
        );

        return $assignedNode;
    }
}
