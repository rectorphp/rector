<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeMethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class MethodParameterTypeResolver
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
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @return Type[]
     */
    public function provideParameterTypesByStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : array
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromStaticCall($staticCall);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return [];
        }
        return $this->provideParameterTypesFromMethodReflection($methodReflection);
    }
    /**
     * @return Type[]
     */
    public function provideParameterTypesByClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $methodReflection = $this->provideByClassMethod($classMethod);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return [];
        }
        return $this->provideParameterTypesFromMethodReflection($methodReflection);
    }
    public function provideByClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PHPStan\Reflection\MethodReflection
    {
        $class = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if (!\is_string($class)) {
            return null;
        }
        $method = $this->nodeNameResolver->getName($classMethod->name);
        if (!\is_string($method)) {
            return null;
        }
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        return $classReflection->getMethod($method, $scope);
    }
    /**
     * @return ParameterReflection[]
     */
    public function getParameterReflectionsFromMethodReflection(\PHPStan\Reflection\MethodReflection $methodReflection) : array
    {
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        return $parametersAcceptor->getParameters();
    }
    /**
     * @return string[]
     */
    public function provideParameterNamesByNew(\PhpParser\Node\Expr\New_ $new) : array
    {
        $objectType = $this->nodeTypeResolver->resolve($new->class);
        $classes = \PHPStan\Type\TypeUtils::getDirectClassNames($objectType);
        $parameterNames = [];
        foreach ($classes as $class) {
            $constructMethodReflection = $this->reflectionResolver->resolveNativeClassMethodReflection($class, \Rector\Core\ValueObject\MethodName::CONSTRUCT);
            if ($constructMethodReflection === null) {
                continue;
            }
            foreach ($constructMethodReflection->getParameters() as $reflectionParameter) {
                $parameterNames[] = $reflectionParameter->getName();
            }
        }
        return $parameterNames;
    }
    /**
     * @return Type[]
     */
    private function provideParameterTypesFromMethodReflection(\PHPStan\Reflection\MethodReflection $methodReflection) : array
    {
        if ($methodReflection instanceof \PHPStan\Reflection\Native\NativeMethodReflection) {
            // method "getParameters()" does not exist there
            return [];
        }
        $parameterTypes = [];
        $parameterReflections = $this->getParameterReflectionsFromMethodReflection($methodReflection);
        foreach ($parameterReflections as $parameterReflection) {
            $parameterTypes[] = $parameterReflection->getType();
        }
        return $parameterTypes;
    }
}
