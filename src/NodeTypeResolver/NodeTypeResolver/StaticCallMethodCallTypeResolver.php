<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
/**
 * @implements NodeTypeResolverInterface<StaticCall|MethodCall>
 */
final class StaticCallMethodCallTypeResolver implements NodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function autowire(NodeTypeResolver $nodeTypeResolver) : void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [StaticCall::class, MethodCall::class];
    }
    /**
     * @param StaticCall|MethodCall $node
     */
    public function resolve(Node $node) : Type
    {
        $methodName = $this->nodeNameResolver->getName($node->name);
        // no specific method found, return class types, e.g. <ClassType>::$method()
        if (!\is_string($methodName)) {
            return new MixedType();
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return new MixedType();
        }
        $nodeReturnType = $scope->getType($node);
        if (!$nodeReturnType instanceof MixedType) {
            return $nodeReturnType;
        }
        if ($node instanceof MethodCall) {
            $callerType = $this->nodeTypeResolver->getType($node->var);
        } else {
            $callerType = $this->nodeTypeResolver->getType($node->class);
        }
        if ($callerType instanceof AliasedObjectType) {
            $callerType = new FullyQualifiedObjectType($callerType->getFullyQualifiedName());
        }
        foreach ($callerType->getObjectClassReflections() as $objectClassReflection) {
            $classMethodReturnType = $this->resolveClassMethodReturnType($objectClassReflection, $node, $methodName, $scope);
            if (!$classMethodReturnType instanceof MixedType) {
                return $classMethodReturnType;
            }
        }
        return new MixedType();
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $node
     */
    private function resolveClassMethodReturnType(ClassReflection $classReflection, $node, string $methodName, Scope $scope) : Type
    {
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if (!$ancestorClassReflection->hasMethod($methodName)) {
                continue;
            }
            $methodReflection = $ancestorClassReflection->getMethod($methodName, $scope);
            if ($methodReflection instanceof PhpMethodReflection) {
                $parametersAcceptorWithPhpDocs = ParametersAcceptorSelectorVariantsWrapper::select($methodReflection, $node, $scope);
                return $parametersAcceptorWithPhpDocs->getReturnType();
            }
        }
        return new MixedType();
    }
}
