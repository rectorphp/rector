<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Enum\ObjectReference;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\NameTypeResolver\NameTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<Name|FullyQualified>
 */
final class NameTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Name::class, FullyQualified::class];
    }
    /**
     * @param Name $node
     */
    public function resolve(Node $node) : Type
    {
        // not instanceof FullyQualified means it is a Name
        if (!$node instanceof FullyQualified && $node->hasAttribute(AttributeKey::NAMESPACED_NAME)) {
            return $this->resolve(new FullyQualified($node->getAttribute(AttributeKey::NAMESPACED_NAME)));
        }
        if ($node->toString() === ObjectReference::PARENT) {
            return $this->resolveParent($node);
        }
        $fullyQualifiedName = $this->resolveFullyQualifiedName($node);
        return new ObjectType($fullyQualifiedName);
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\Name\FullyQualified $node
     */
    private function resolveClassReflection($node) : ?ClassReflection
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        return $scope->getClassReflection();
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\ObjectType|\PHPStan\Type\UnionType
     */
    private function resolveParent(Name $name)
    {
        $classReflection = $this->resolveClassReflection($name);
        if (!$classReflection instanceof ClassReflection || !$classReflection->isClass()) {
            return new MixedType();
        }
        if ($classReflection->isAnonymous()) {
            return new MixedType();
        }
        $parentClassObjectTypes = [];
        foreach ($classReflection->getParents() as $parentClassReflection) {
            $parentClassObjectTypes[] = new ObjectType($parentClassReflection->getName());
        }
        if ($parentClassObjectTypes === []) {
            return new MixedType();
        }
        if (\count($parentClassObjectTypes) === 1) {
            return $parentClassObjectTypes[0];
        }
        return new UnionType($parentClassObjectTypes);
    }
    private function resolveFullyQualifiedName(Name $name) : string
    {
        $nameValue = $name->toString();
        if (\in_array($nameValue, [ObjectReference::SELF, ObjectReference::STATIC], \true)) {
            $classReflection = $this->resolveClassReflection($name);
            if (!$classReflection instanceof ClassReflection || $classReflection->isAnonymous()) {
                return $name->toString();
            }
            return $classReflection->getName();
        }
        return $nameValue;
    }
}
