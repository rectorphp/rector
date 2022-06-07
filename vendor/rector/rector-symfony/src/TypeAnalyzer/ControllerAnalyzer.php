<?php

declare (strict_types=1);
namespace Rector\Symfony\TypeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
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
    public function isController(Expr $expr) : bool
    {
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        // might be missing in a trait
        if (!$scope instanceof Scope) {
            return \false;
        }
        $nodeType = $scope->getType($expr);
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
}
