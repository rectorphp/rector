<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\ReturnTypeExtension;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPStanExtensions\TypeResolver\GetNameMethodCallTypeResolver;

/**
 * @see \Rector\NodeNameResolver\NodeNameResolver::getName()
 *
 * These returns always strings for nodes with required names, e.g. for @see ClassMethod
 */
final class NameResolverReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @var GetNameMethodCallTypeResolver
     */
    private $methodCallTypeResolver;

    public function __construct(GetNameMethodCallTypeResolver $methodCallTypeResolver)
    {
        $this->methodCallTypeResolver = $methodCallTypeResolver;
    }

    public function getClass(): string
    {
        return NodeNameResolver::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getName';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        return $this->methodCallTypeResolver->resolveFromMethodCall($methodReflection, $methodCall, $scope);
    }
}
