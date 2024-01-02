<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @implements NodeTypeResolverInterface<ClassMethod|ClassConst>
 */
final class ClassMethodOrClassConstTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [ClassMethod::class, ClassConst::class];
    }
    /**
     * @param ClassMethod|ClassConst $node
     */
    public function resolve(Node $node) : Type
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            // anonymous class
            return new ObjectWithoutClassType();
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection || $classReflection->isAnonymous()) {
            // anonymous class
            return new ObjectWithoutClassType();
        }
        return new ObjectType($classReflection->getName());
    }
}
