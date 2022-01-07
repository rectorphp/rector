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
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ControllerAnalyzer
{
    public function isController(\PhpParser\Node\Expr $expr) : bool
    {
        $scope = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        // might be missing in a trait
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $nodeType = $scope->getType($expr);
        if (!$nodeType instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        if ($nodeType instanceof \PHPStan\Type\ThisType) {
            $nodeType = $nodeType->getStaticObjectType();
        }
        if (!$nodeType instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        $classReflection = $nodeType->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        return $this->isControllerClassReflection($classReflection);
    }
    public function isInsideController(\PhpParser\Node $node) : bool
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        // might be missing in a trait
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        return $this->isControllerClassReflection($classReflection);
    }
    private function isControllerClassReflection(\PHPStan\Reflection\ClassReflection $classReflection) : bool
    {
        if ($classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller')) {
            return \true;
        }
        return $classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController');
    }
}
