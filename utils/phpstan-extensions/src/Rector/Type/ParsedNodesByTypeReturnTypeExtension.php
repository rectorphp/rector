<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rector\Type;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeContainer\ParsedNodesByType;

final class ParsedNodesByTypeReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return ParsedNodesByType::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getNodesByType';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

        $argumentValue = $this->resolveArgumentValue($methodCall->args[0]->value);
        if ($argumentValue === null) {
            return $returnType;
        }

        return new ArrayType(new MixedType(), new ObjectType($argumentValue));
    }

    private function resolveArgumentValue(Expr $expr): ?string
    {
        if ($expr instanceof ClassConstFetch) {
            if ($expr->class instanceof Name) {
                return $expr->class->toString();
            }
        }

        return null;
    }
}
