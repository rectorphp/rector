<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\ReturnTypeExtension\Builder;

use PhpParser\Builder\Class_ as ClassBuilder;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class ClassBuilderReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return ClassBuilder::class;
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
        return new FullyQualifiedObjectType(Class_::class);
    }
}
