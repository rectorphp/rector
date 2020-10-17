<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ConstructorAssignPropertyAnalyzer
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function resolveConstructorAssign(Property $property): ?Assign
    {
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod === null) {
            return null;
        }

        $propertyName = $this->nodeNameResolver->getName($property);
        if ($propertyName === null) {
            return null;
        }

        return $this->betterNodeFinder->findFirst((array) $constructClassMethod->stmts, function (Node $node) use (
            $propertyName
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->isLocalPropertyNamed($node->var, $propertyName)) {
                return null;
            }

            return $node;
        });
    }

    private function isLocalPropertyNamed(Node $node, string $propertyName): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        if (! $node->var instanceof Variable) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($node->var, 'this')) {
            return false;
        }

        return $this->nodeNameResolver->isName($node->name, $propertyName);
    }
}
