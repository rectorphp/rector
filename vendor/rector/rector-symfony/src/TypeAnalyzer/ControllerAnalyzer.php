<?php

declare (strict_types=1);
namespace Rector\Symfony\TypeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ControllerAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Stmt\Class_ $node
     */
    public function isController($node) : bool
    {
        if ($node instanceof Class_) {
            return $this->isControllerClass($node);
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        // might be missing in a trait
        if (!$scope instanceof Scope) {
            return \false;
        }
        $nodeType = $scope->getType($node);
        if (!$nodeType instanceof TypeWithClassName) {
            return \false;
        }
        if ($nodeType instanceof ThisType) {
            $nodeType = $nodeType->getStaticObjectType();
        }
        if (!$nodeType instanceof ObjectType) {
            return \false;
        }
        $classReflection = $nodeType->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $this->isControllerClassReflection($classReflection);
    }
    public function isInsideController(Node $node) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $this->isControllerClassReflection($classReflection);
    }
    private function isControllerClassReflection(ClassReflection $classReflection) : bool
    {
        if ($classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller')) {
            return \true;
        }
        return $classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController');
    }
    private function isControllerClass(Class_ $class) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $this->isControllerClassReflection($classReflection);
    }
}
