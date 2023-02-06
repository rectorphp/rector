<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\WrapperPropertyReflection;
use PHPStan\Type\MixedType;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class PropertyFetchTypeAnalyzer
{
    public function isPropertyFetchExprNotNativelyTyped(Expr $expr) : bool
    {
        if (!$expr instanceof PropertyFetch) {
            return \false;
        }
        if (!$expr->name instanceof Identifier) {
            return \false;
        }
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $propertyName = $expr->name->toString();
        $propertyHolderType = $scope->getType($expr->var);
        if (!$propertyHolderType->hasProperty($propertyName)->yes()) {
            return \false;
        }
        $propertyReflection = $propertyHolderType->getProperty($propertyName, $scope);
        $phpPropertyReflection = $this->getNativeReflectionForProperty($propertyReflection);
        if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
            return \false;
        }
        return $phpPropertyReflection->getNativeType() instanceof MixedType;
    }
    private function getNativeReflectionForProperty(PropertyReflection $propertyReflection) : ?PhpPropertyReflection
    {
        $reflection = $propertyReflection;
        while ($reflection instanceof WrapperPropertyReflection) {
            $reflection = $reflection->getOriginalReflection();
        }
        if (!$reflection instanceof PhpPropertyReflection) {
            return null;
        }
        return $reflection;
    }
}
