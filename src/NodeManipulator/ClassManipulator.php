<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PostRector\Collector\NodesToRemoveCollector;
final class ClassManipulator
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer
     */
    private $classChildAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, NodesToRemoveCollector $nodesToRemoveCollector, ReflectionProvider $reflectionProvider, ClassChildAnalyzer $classChildAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->reflectionProvider = $reflectionProvider;
        $this->classChildAnalyzer = $classChildAnalyzer;
    }
    public function hasParentMethodOrInterface(ObjectType $objectType, string $oldMethod, string $newMethod) : bool
    {
        if (!$this->reflectionProvider->hasClass($objectType->getClassName())) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
        $ancestorClassReflections = \array_merge($classReflection->getParents(), $classReflection->getInterfaces());
        foreach ($ancestorClassReflections as $ancestorClassReflection) {
            if (!$ancestorClassReflection->hasMethod($oldMethod)) {
                continue;
            }
            if ($this->classChildAnalyzer->hasChildClassMethod($ancestorClassReflection, $newMethod)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @return string[]
     */
    public function getPrivatePropertyNames(Class_ $class) : array
    {
        $privateProperties = \array_filter($class->getProperties(), static function (Property $property) : bool {
            return $property->isPrivate();
        });
        return $this->nodeNameResolver->getNames($privateProperties);
    }
    public function hasTrait(Class_ $class, string $desiredTrait) : bool
    {
        foreach ($class->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $traitName) {
                if (!$this->nodeNameResolver->isName($traitName, $desiredTrait)) {
                    continue;
                }
                return \true;
            }
        }
        return \false;
    }
    public function replaceTrait(Class_ $class, string $oldTrait, string $newTrait) : void
    {
        foreach ($class->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $key => $traitTrait) {
                if (!$this->nodeNameResolver->isName($traitTrait, $oldTrait)) {
                    continue;
                }
                $traitUse->traits[$key] = new FullyQualified($newTrait);
                break;
            }
        }
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_ $classLike
     */
    public function getClassLikeNodeParentInterfaceNames($classLike) : array
    {
        if ($classLike instanceof Class_) {
            return $this->nodeNameResolver->getNames($classLike->implements);
        }
        return $this->nodeNameResolver->getNames($classLike->extends);
    }
    public function removeInterface(Class_ $class, string $desiredInterface) : void
    {
        foreach ($class->implements as $implement) {
            if (!$this->nodeNameResolver->isName($implement, $desiredInterface)) {
                continue;
            }
            $this->nodesToRemoveCollector->addNodeToRemove($implement);
        }
    }
}
