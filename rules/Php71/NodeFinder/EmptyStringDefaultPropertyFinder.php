<?php

declare(strict_types=1);

namespace Rector\Php71\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class EmptyStringDefaultPropertyFinder
{
    /**
     * @var array<string, PropertyProperty[]>
     */
    private array $propertyPropertiesByClassName = [];

    public function __construct(
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    /**
     * @return PropertyProperty[]
     */
    public function find(Assign $assign): array
    {
        $classLike = $assign->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return [];
        }

        $className = $assign->getAttribute(AttributeKey::CLASS_NAME);
        if (! is_string($className)) {
            return [];
        }

        if (isset($this->propertyPropertiesByClassName[$className])) {
            return $this->propertyPropertiesByClassName[$className];
        }

        /** @var PropertyProperty[] $propertyProperties */
        $propertyProperties = $this->betterNodeFinder->find($classLike, function (Node $node): bool {
            if (! $node instanceof PropertyProperty) {
                return false;
            }

            if ($node->default === null) {
                return false;
            }

            return $this->isEmptyString($node->default);
        });

        $this->propertyPropertiesByClassName[$className] = $propertyProperties;

        return $propertyProperties;
    }

    private function isEmptyString(Expr $expr): bool
    {
        if (! $expr instanceof String_) {
            return false;
        }

        return $expr->value === '';
    }
}
