<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
/**
 * Utils for PropertyFetch Node:
 * "$this->property"
 */
final class MagicPropertyFetchAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isMagicOnType(PropertyFetch $propertyFetch, ObjectType $objectType) : bool
    {
        $varNodeType = $this->nodeTypeResolver->getType($propertyFetch);
        if ($varNodeType instanceof ErrorType) {
            return \true;
        }
        if ($varNodeType instanceof MixedType) {
            return \false;
        }
        if ($varNodeType->isSuperTypeOf($objectType)->yes()) {
            return \false;
        }
        $nodeName = $this->nodeNameResolver->getName($propertyFetch->name);
        if ($nodeName === null) {
            return \false;
        }
        return !$this->hasPublicProperty($propertyFetch, $nodeName);
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $expr
     */
    private function hasPublicProperty($expr, string $propertyName) : bool
    {
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            throw new ShouldNotHappenException();
        }
        if ($expr instanceof PropertyFetch) {
            $propertyFetchType = $scope->getType($expr->var);
        } else {
            $propertyFetchType = $this->nodeTypeResolver->getType($expr->class);
        }
        if (!$propertyFetchType instanceof TypeWithClassName) {
            return \false;
        }
        $propertyFetchType = $propertyFetchType->getClassName();
        if (!$this->reflectionProvider->hasClass($propertyFetchType)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($propertyFetchType);
        if (!$classReflection->hasProperty($propertyName)) {
            return \false;
        }
        $propertyReflection = $classReflection->getProperty($propertyName, $scope);
        return $propertyReflection->isPublic();
    }
}
