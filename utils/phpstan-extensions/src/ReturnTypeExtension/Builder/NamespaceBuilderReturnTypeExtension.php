<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\ReturnTypeExtension\Builder;

use PhpParser\Builder\Namespace_ as NamespaceBuilder;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class NamespaceBuilderReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return NamespaceBuilder::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getNode';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        return new FullyQualifiedObjectType(Namespace_::class);
    }
}
