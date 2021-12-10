<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\NameTypeResolver\NameTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<Name|FullyQualified>
 */
final class NameTypeResolver implements \Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface
{
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
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [\PhpParser\Node\Name::class, \PhpParser\Node\Name\FullyQualified::class];
    }
    /**
     * @param Name $node
     */
    public function resolve(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        if ($node->toString() === \Rector\Core\Enum\ObjectReference::PARENT()->getValue()) {
            return $this->resolveParent($node);
        }
        $fullyQualifiedName = $this->resolveFullyQualifiedName($node);
        if ($node->toString() === 'array') {
            return new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
        }
        return new \PHPStan\Type\ObjectType($fullyQualifiedName);
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\ObjectType|\PHPStan\Type\UnionType
     */
    private function resolveParent(\PhpParser\Node\Name $name)
    {
        $class = $this->betterNodeFinder->findParentType($name, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return new \PHPStan\Type\MixedType();
        }
        $className = $this->nodeNameResolver->getName($class);
        if (!\is_string($className)) {
            return new \PHPStan\Type\MixedType();
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return new \PHPStan\Type\MixedType();
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $parentClassObjectTypes = [];
        foreach ($classReflection->getParents() as $parentClassReflection) {
            $parentClassObjectTypes[] = new \PHPStan\Type\ObjectType($parentClassReflection->getName());
        }
        if ($parentClassObjectTypes === []) {
            return new \PHPStan\Type\MixedType();
        }
        if (\count($parentClassObjectTypes) === 1) {
            return $parentClassObjectTypes[0];
        }
        return new \PHPStan\Type\UnionType($parentClassObjectTypes);
    }
    private function resolveFullyQualifiedName(\PhpParser\Node\Name $name) : string
    {
        $nameValue = $name->toString();
        if (\in_array($nameValue, [\Rector\Core\Enum\ObjectReference::SELF()->getValue(), \Rector\Core\Enum\ObjectReference::STATIC()->getValue(), 'this'], \true)) {
            $classLike = $this->betterNodeFinder->findParentType($name, \PhpParser\Node\Stmt\ClassLike::class);
            if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
                return $name->toString();
            }
            return (string) $this->nodeNameResolver->getName($classLike);
        }
        /** @var Name|null $resolvedNameNode */
        $resolvedNameNode = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::RESOLVED_NAME);
        if ($resolvedNameNode instanceof \PhpParser\Node\Name) {
            return $resolvedNameNode->toString();
        }
        return $nameValue;
    }
}
