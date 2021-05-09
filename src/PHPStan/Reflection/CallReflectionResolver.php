<?php

declare (strict_types=1);
namespace Rector\Core\PHPStan\Reflection;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverRegistry;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class CallReflectionResolver
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var TypeToCallReflectionResolverRegistry
     */
    private $typeToCallReflectionResolverRegistry;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverRegistry $typeToCallReflectionResolverRegistry)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->typeToCallReflectionResolverRegistry = $typeToCallReflectionResolverRegistry;
    }
    public function resolveConstructor(\PhpParser\Node\Expr\New_ $new) : ?\PHPStan\Reflection\MethodReflection
    {
        $scope = $new->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $classType = $this->nodeTypeResolver->resolve($new->class);
        if ($classType instanceof \PHPStan\Type\UnionType) {
            return $this->matchConstructorMethodInUnionType($classType, $scope);
        }
        if (!$classType->hasMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT)->yes()) {
            return null;
        }
        return $classType->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT, $scope);
    }
    /**
     * @param FuncCall|MethodCall|StaticCall $node
     * @return MethodReflection|FunctionReflection|null
     */
    public function resolveCall(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->resolveFunctionCall($node);
        }
        return $this->resolveMethodCall($node);
    }
    /**
     * @param FunctionReflection|MethodReflection|null $reflection
     * @param FuncCall|MethodCall|StaticCall|New_ $node
     */
    public function resolveParametersAcceptor($reflection, \PhpParser\Node $node) : ?\PHPStan\Reflection\ParametersAcceptor
    {
        if ($reflection === null) {
            return null;
        }
        return $reflection->getVariants()[0];
    }
    private function matchConstructorMethodInUnionType(\PHPStan\Type\UnionType $unionType, \PHPStan\Analyser\Scope $scope) : ?\PHPStan\Reflection\MethodReflection
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (!$unionedType instanceof \PHPStan\Type\TypeWithClassName) {
                continue;
            }
            if (!$unionedType->hasMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT)->yes()) {
                continue;
            }
            return $unionedType->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT, $scope);
        }
        return null;
    }
    /**
     * @return FunctionReflection|MethodReflection|null
     */
    private function resolveFunctionCall(\PhpParser\Node\Expr\FuncCall $funcCall)
    {
        /** @var Scope|null $scope */
        $scope = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if ($funcCall->name instanceof \PhpParser\Node\Name) {
            if ($this->reflectionProvider->hasFunction($funcCall->name, $scope)) {
                return $this->reflectionProvider->getFunction($funcCall->name, $scope);
            }
            return null;
        }
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $funcCallNameType = $scope->getType($funcCall->name);
        return $this->typeToCallReflectionResolverRegistry->resolve($funcCallNameType, $scope);
    }
    /**
     * @param MethodCall|StaticCall $expr
     */
    private function resolveMethodCall(\PhpParser\Node\Expr $expr) : ?\PHPStan\Reflection\MethodReflection
    {
        $scope = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $methodName = $this->nodeNameResolver->getName($expr->name);
        if ($methodName === null) {
            return null;
        }
        $classType = $this->nodeTypeResolver->resolve($expr instanceof \PhpParser\Node\Expr\MethodCall ? $expr->var : $expr->class);
        if ($classType instanceof \PHPStan\Type\ThisType) {
            $classType = $classType->getStaticObjectType();
        }
        if ($classType instanceof \PHPStan\Type\ObjectType) {
            if (!$this->reflectionProvider->hasClass($classType->getClassName())) {
                return null;
            }
            $classReflection = $this->reflectionProvider->getClass($classType->getClassName());
            if ($classReflection->hasMethod($methodName)) {
                return $classReflection->getMethod($methodName, $scope);
            }
        }
        return null;
    }
}
