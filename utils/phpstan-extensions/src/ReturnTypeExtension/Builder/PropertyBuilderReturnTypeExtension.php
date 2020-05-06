<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\ReturnTypeExtension\Builder;

use PhpParser\Builder\Property as PropertyBuilder;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class PropertyBuilderReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return PropertyBuilder::class;
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
        return new FullyQualifiedObjectType(Property::class);
    }
}
