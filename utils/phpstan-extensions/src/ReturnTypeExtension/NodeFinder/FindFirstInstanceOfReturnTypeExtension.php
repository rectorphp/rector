<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\ReturnTypeExtension\NodeFinder;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class FindFirstInstanceOfReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return BetterNodeFinder::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(
            $methodReflection->getName(),
            ['findFirstInstanceOf', 'findFirstParentInstanceOf', 'findFirstAncestorInstanceOf'],
            true
        );
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        $secondArgumentNode = $methodCall->args[1]->value;

        // fallback
        if ($this->shouldFallbackToResolvedType($secondArgumentNode)) {
            return $returnType;
        }

        /** @var ClassConstFetch $secondArgumentNode */
        $class = $secondArgumentNode->class->toString();

        return new UnionType([new NullType(), new ObjectType($class)]);
    }

    private function shouldFallbackToResolvedType(Expr $expr): bool
    {
        if (! $expr instanceof ClassConstFetch) {
            return true;
        }

        if (! $expr->class instanceof Name) {
            return true;
        }

        return (string) $expr->name !== 'class';
    }
}
