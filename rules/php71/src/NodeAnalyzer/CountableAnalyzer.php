<?php

declare(strict_types=1);

namespace Rector\Php71\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use ReflectionClass;

final class CountableAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isCastableArrayType(Expr $expr): bool
    {
        if (! $expr instanceof PropertyFetch) {
            return false;
        }

        $callerObjectType = $this->nodeTypeResolver->resolve($expr->var);

        $propertyName = $this->nodeNameResolver->getName($expr->name);
        if (! is_string($propertyName)) {
            return false;
        }

        if ($callerObjectType instanceof UnionType) {
            $callerObjectType = $callerObjectType->getTypes()[0];
        }

        if (! $callerObjectType instanceof TypeWithClassName) {
            return false;
        }

        if (is_a($callerObjectType->getClassName(), 'PhpParser\Node\Stmt', true)) {
            return false;
        }

        if (is_a($callerObjectType->getClassName(), Array_::class, true)) {
            return false;
        }

        // this must be handled reflection, as PHPStan ReflectionProvider does not provide default values for properties in any way
        $reflectionClass = new ReflectionClass($callerObjectType->getClassName());
        $propertiesDefaults = $reflectionClass->getDefaultProperties();

        if (! array_key_exists($propertyName, $propertiesDefaults)) {
            return false;
        }

        $propertyDefaultValue = $propertiesDefaults[$propertyName];
        return $propertyDefaultValue === null;
    }
}
