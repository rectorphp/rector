<?php

declare (strict_types=1);
namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class PhpAttributeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
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
    public function __construct(\Rector\Core\PhpParser\AstResolver $astResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->astResolver = $astResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Param $node
     */
    public function hasPhpAttribute($node, string $attributeClass) : bool
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$this->nodeNameResolver->isName($attribute->name, $attributeClass)) {
                    continue;
                }
                return \true;
            }
        }
        return \false;
    }
    public function hasInheritedPhpAttribute(\PhpParser\Node\Stmt\ClassLike $classLike, string $attributeClass) : bool
    {
        $className = (string) $this->nodeNameResolver->getName($classLike);
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $ancestorClassReflections = \array_merge($classReflection->getParents(), $classReflection->getInterfaces());
        foreach ($ancestorClassReflections as $ancestorClassReflection) {
            $ancestorClassName = $ancestorClassReflection->getName();
            $class = $this->astResolver->resolveClassFromName($ancestorClassName);
            if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
                continue;
            }
            if ($this->hasPhpAttribute($class, $attributeClass)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param string[] $attributeClasses
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Param $node
     */
    public function hasPhpAttributes($node, array $attributeClasses) : bool
    {
        foreach ($attributeClasses as $attributeClass) {
            if ($this->hasPhpAttribute($node, $attributeClass)) {
                return \true;
            }
        }
        return \false;
    }
}
