<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rector\Type;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class PhpDocInfoGetByTypeReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return PhpDocInfo::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getByType';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $returnType = $this->resolveArgumentValue($methodCall->args[0]->value);

        if ($returnType === null) {
            return new MixedType();
        }

        return new UnionType([new ObjectType($returnType), new NullType()]);
    }

    private function resolveArgumentValue(Expr $expr): ?string
    {
        if ($expr instanceof ClassConstFetch) {
            if ((string) $expr->name !== 'class') {
                return null;
            }

            return $expr->class->toString();
        }

        return null;
    }
}
