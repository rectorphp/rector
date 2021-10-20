<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20211020\Symfony\Contracts\Service\Attribute\Required;
final class StaticCallMethodCallTypeResolver implements \Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @required
     */
    public function autowireStaticCallMethodCallTypeResolver(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver) : void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param StaticCall|MethodCall $node
     */
    public function resolve($node) : \PHPStan\Type\Type
    {
        $methodName = $this->nodeNameResolver->getName($node->name);
        // no specific method found, return class types, e.g. <ClassType>::$method()
        if (!\is_string($methodName)) {
            return new \PHPStan\Type\MixedType();
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return new \PHPStan\Type\MixedType();
        }
        $nodeReturnType = $scope->getType($node);
        if (!$nodeReturnType instanceof \PHPStan\Type\MixedType) {
            return $nodeReturnType;
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            $callerType = $this->nodeTypeResolver->getType($node->var);
        } else {
            $callerType = $this->nodeTypeResolver->getType($node->class);
        }
        foreach ($callerType->getReferencedClasses() as $referencedClass) {
            $classMethodReturnType = $this->resolveClassMethodReturnType($referencedClass, $methodName, $scope);
            if (!$classMethodReturnType instanceof \PHPStan\Type\MixedType) {
                return $classMethodReturnType;
            }
        }
        return new \PHPStan\Type\MixedType();
    }
    private function resolveClassMethodReturnType(string $referencedClass, string $methodName, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        if (!$this->reflectionProvider->hasClass($referencedClass)) {
            return new \PHPStan\Type\MixedType();
        }
        $classReflection = $this->reflectionProvider->getClass($referencedClass);
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if (!$ancestorClassReflection->hasMethod($methodName)) {
                continue;
            }
            $methodReflection = $ancestorClassReflection->getMethod($methodName, $scope);
            if ($methodReflection instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
                $parametersAcceptorWithPhpDocs = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
                return $parametersAcceptorWithPhpDocs->getReturnType();
            }
        }
        return new \PHPStan\Type\MixedType();
    }
}
