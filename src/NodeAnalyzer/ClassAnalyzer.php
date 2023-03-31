<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ClassAnalyzer
{
    public function isAnonymousClass(Node $node) : bool
    {
        if (!$node instanceof Class_) {
            return \false;
        }
        if ($node->isAnonymous()) {
            return \true;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if ($classReflection instanceof ClassReflection) {
            return $classReflection->isAnonymous();
        }
        return \false;
    }
}
