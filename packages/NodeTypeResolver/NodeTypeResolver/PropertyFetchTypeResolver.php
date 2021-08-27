<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;
use RectorPrefix20210827\Symfony\Contracts\Service\Attribute\Required;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\PropertyFetchTypeResolverTest
 */
final class PropertyFetchTypeResolver implements \Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector
     */
    private $traitNodeScopeCollector;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector $traitNodeScopeCollector, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->traitNodeScopeCollector = $traitNodeScopeCollector;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @required
     */
    public function autowirePropertyFetchTypeResolver(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver) : void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [\PhpParser\Node\Expr\PropertyFetch::class];
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : \PHPStan\Type\Type
    {
        // compensate 3rd party non-analysed property reflection
        $vendorPropertyType = $this->getVendorPropertyFetchType($node);
        if (!$vendorPropertyType instanceof \PHPStan\Type\MixedType) {
            return $vendorPropertyType;
        }
        /** @var Scope|null $scope */
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            $classNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
            if ($classNode instanceof \PhpParser\Node\Stmt\Trait_) {
                /** @var string $traitName */
                $traitName = $classNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
                $scope = $this->traitNodeScopeCollector->getScopeForTrait($traitName);
            }
        }
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            $classNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
            // fallback to class, since property fetches are not scoped by PHPStan
            if ($classNode instanceof \PhpParser\Node\Stmt\ClassLike) {
                $scope = $classNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            }
            if (!$scope instanceof \PHPStan\Analyser\Scope) {
                return new \PHPStan\Type\MixedType();
            }
        }
        return $scope->getType($node);
    }
    private function getVendorPropertyFetchType(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : \PHPStan\Type\Type
    {
        // 3rd party code
        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if ($propertyName === null) {
            return new \PHPStan\Type\MixedType();
        }
        $varType = $this->nodeTypeResolver->resolve($propertyFetch->var);
        if (!$varType instanceof \PHPStan\Type\ObjectType) {
            return new \PHPStan\Type\MixedType();
        }
        if (!$this->reflectionProvider->hasClass($varType->getClassName())) {
            return new \PHPStan\Type\MixedType();
        }
        $classReflection = $this->reflectionProvider->getClass($varType->getClassName());
        if (!$classReflection->hasProperty($propertyName)) {
            return new \PHPStan\Type\MixedType();
        }
        $propertyFetchScope = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if ($propertyFetchScope === null) {
            return new \PHPStan\Type\MixedType();
        }
        $propertyReflection = $classReflection->getProperty($propertyName, $propertyFetchScope);
        return $propertyReflection->getReadableType();
    }
}
