<?php

declare (strict_types=1);
namespace Rector\Php82\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionProperty;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Php81\Enum\AttributeName;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\Visibility;
final class ReadonlyClassManipulator
{
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(VisibilityManipulator $visibilityManipulator, PhpAttributeAnalyzer $phpAttributeAnalyzer, ReflectionProvider $reflectionProvider)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function process(Class_ $class): ?\PhpParser\Node\Stmt\Class_
    {
        $parentClassReflections = $this->resolveParentClassReflections($class);
        if ($this->shouldSkip($class, $parentClassReflections)) {
            return null;
        }
        $this->visibilityManipulator->makeReadonly($class);
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod instanceof ClassMethod) {
            foreach ($constructClassMethod->getParams() as $param) {
                $this->visibilityManipulator->removeReadonly($param);
            }
        }
        foreach ($class->getProperties() as $property) {
            $this->visibilityManipulator->removeReadonly($property);
        }
        return $class;
    }
    /**
     * Resolve parents from the extends name, so anonymous classes (no name) work too.
     *
     * @return ClassReflection[]
     */
    private function resolveParentClassReflections(Class_ $class): array
    {
        if (!$class->extends instanceof Name) {
            return [];
        }
        $parentName = $class->extends->toString();
        if (!$this->reflectionProvider->hasClass($parentName)) {
            return [];
        }
        $parentClassReflection = $this->reflectionProvider->getClass($parentName);
        return array_merge([$parentClassReflection], $parentClassReflection->getParents());
    }
    /**
     * @param Property[] $properties
     */
    private function hasNonTypedProperty(array $properties): bool
    {
        $found = \false;
        foreach ($properties as $property) {
            if (!$property->type instanceof Node) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    /**
     * @param ClassReflection[] $parentClassReflections
     */
    private function shouldSkip(Class_ $class, array $parentClassReflections): bool
    {
        if ($this->shouldSkipClass($class)) {
            return \true;
        }
        if (!$class->isAnonymous() && !$class->isFinal()) {
            return !$this->isExtendsReadonlyClass($parentClassReflections);
        }
        foreach ($parentClassReflections as $parentClassReflection) {
            if (!$parentClassReflection->isReadOnly()) {
                return \true;
            }
        }
        $properties = $class->getProperties();
        if ($this->hasWritableProperty($properties)) {
            return \true;
        }
        if ($this->hasNonTypedProperty($properties)) {
            return \true;
        }
        if ($this->shouldSkipConsumeTraitProperty($class)) {
            return \true;
        }
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            // no __construct means no property promotion, skip if class has no property defined
            return $properties === [];
        }
        $params = $constructClassMethod->getParams();
        if ($params === []) {
            // no params means no property promotion, skip if class has no property defined
            return $properties === [];
        }
        return $this->shouldSkipParams($params);
    }
    private function shouldSkipConsumeTraitProperty(Class_ $class): bool
    {
        $traitUses = $class->getTraitUses();
        foreach ($traitUses as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $traitName = $trait->toString();
                // trait not autoloaded
                if (!$this->reflectionProvider->hasClass($traitName)) {
                    return \true;
                }
                $traitClassReflection = $this->reflectionProvider->getClass($traitName);
                $nativeReflection = $traitClassReflection->getNativeReflection();
                if ($this->hasReadonlyProperty($nativeReflection->getProperties())) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * @param ReflectionProperty[] $properties
     */
    private function hasReadonlyProperty(array $properties): bool
    {
        $found = \false;
        foreach ($properties as $reflectionProperty) {
            if (!$reflectionProperty->isReadOnly()) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    /**
     * @param ClassReflection[] $parents
     */
    private function isExtendsReadonlyClass(array $parents): bool
    {
        $found = \false;
        foreach ($parents as $classReflection) {
            if ($classReflection->isReadOnly()) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    /**
     * @param Property[] $properties
     */
    private function hasWritableProperty(array $properties): bool
    {
        $found = \false;
        foreach ($properties as $property) {
            if (!$property->isReadonly()) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    private function shouldSkipClass(Class_ $class): bool
    {
        // need to have test fixture once feature added to  nikic/PHP-Parser
        if ($this->visibilityManipulator->hasVisibility($class, Visibility::READONLY)) {
            return \true;
        }
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($class, AttributeName::ALLOW_DYNAMIC_PROPERTIES)) {
            return \true;
        }
        return $class->extends instanceof FullyQualified && !$this->reflectionProvider->hasClass($class->extends->toString());
    }
    /**
     * @param Param[] $params
     */
    private function shouldSkipParams(array $params): bool
    {
        foreach ($params as $param) {
            // has non-readonly property promotion
            if (!$this->visibilityManipulator->hasVisibility($param, Visibility::READONLY) && $param->isPromoted()) {
                return \true;
            }
            // type is missing, invalid syntax
            if ($param->type === null) {
                return \true;
            }
        }
        return \false;
    }
}
