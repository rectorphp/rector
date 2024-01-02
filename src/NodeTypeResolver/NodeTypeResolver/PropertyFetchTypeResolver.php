<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\PropertyFetchTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<PropertyFetch>
 */
final class PropertyFetchTypeResolver implements NodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function autowire(NodeTypeResolver $nodeTypeResolver) : void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [PropertyFetch::class];
    }
    /**
     * @param PropertyFetch $node
     */
    public function resolve(Node $node) : Type
    {
        // compensate 3rd party non-analysed property reflection
        $vendorPropertyType = $this->getVendorPropertyFetchType($node);
        if (!$vendorPropertyType instanceof MixedType) {
            return $vendorPropertyType;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return new MixedType();
        }
        return $scope->getType($node);
    }
    private function getVendorPropertyFetchType(PropertyFetch $propertyFetch) : Type
    {
        // 3rd party code
        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if ($propertyName === null) {
            return new MixedType();
        }
        $varType = $this->nodeTypeResolver->getType($propertyFetch->var);
        if (!$varType instanceof ObjectType) {
            return new MixedType();
        }
        if (!$this->reflectionProvider->hasClass($varType->getClassName())) {
            return new MixedType();
        }
        $classReflection = $this->reflectionProvider->getClass($varType->getClassName());
        if (!$classReflection->hasProperty($propertyName)) {
            return new MixedType();
        }
        $propertyFetchScope = $propertyFetch->getAttribute(AttributeKey::SCOPE);
        if (!$propertyFetchScope instanceof Scope) {
            return new MixedType();
        }
        $propertyReflection = $classReflection->getProperty($propertyName, $propertyFetchScope);
        return $propertyReflection->getReadableType();
    }
}
