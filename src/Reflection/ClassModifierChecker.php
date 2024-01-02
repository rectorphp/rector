<?php

declare (strict_types=1);
namespace Rector\Reflection;

use PhpParser\Node;
use PHPStan\Reflection\ClassReflection;
final class ClassModifierChecker
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isInsideFinalClass(Node $node) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->isFinalByKeyword();
    }
    public function isInsideAbstractClass(Node $node) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->isAbstract();
    }
}
