<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\ReturnTypeExtension;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

/**
 * Covers:
 * - NodeFinder::findInstanceOf()
 * - NodeFinder::findFirstInstanceOf()
 */
final class FindInstanceOfReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return NodeFinder::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['findInstanceOf', 'findFirstInstanceOf'], true);
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

        $secondArgumentNode = $methodCall->args[1]->value;
        if (! $secondArgumentNode instanceof ClassConstFetch) {
            return $returnType;
        }

        if (! $secondArgumentNode->class instanceof Name) {
            return $returnType;
        }

        $class = $secondArgumentNode->class->toString();

        if ($methodReflection->getName() === 'findFirstInstanceOf') {
            return new UnionType([new ObjectType($class), new NullType()]);
        }

        return new ArrayType(new MixedType(), new ObjectType($class));
    }
}
