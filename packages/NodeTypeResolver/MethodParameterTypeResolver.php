<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeMethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Type;
use Rector\Core\Reflection\ReflectionResolver;
final class MethodParameterTypeResolver
{
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
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
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromClassMethod($classMethod);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return [];
        }
        return $this->provideParameterTypesFromMethodReflection($methodReflection);
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
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            $parameterTypes[] = $parameterReflection->getType();
        }
        return $parameterTypes;
    }
}
