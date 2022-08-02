<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix202208\Symfony\Contracts\Service\Attribute\Required;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\PropertyFetchTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<PropertyFetch>
 */
final class PropertyFetchTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
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
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @required
     */
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
            $classLike = $this->betterNodeFinder->findParentType($node, ClassLike::class);
            // fallback to class, since property fetches are not scoped by PHPStan
            if ($classLike instanceof ClassLike) {
                $scope = $classLike->getAttribute(AttributeKey::SCOPE);
            }
            if (!$scope instanceof Scope) {
                return new MixedType();
            }
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
        if ($propertyFetchScope === null) {
            return new MixedType();
        }
        $propertyReflection = $classReflection->getProperty($propertyName, $propertyFetchScope);
        return $propertyReflection->getReadableType();
    }
}
