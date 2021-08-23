<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeTypeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Type;
use Rector\Core\Reflection\ReflectionResolver;
final class CallTypeAnalyzer
{
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @return Type[]
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    public function resolveMethodParameterTypes($call) : array
    {
        $methodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($call);
        if ($methodReflection === null) {
            return [];
        }
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        $parameterTypes = [];
        /** @var ParameterReflection $parameterReflection */
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            $parameterTypes[] = $parameterReflection->getType();
        }
        return $parameterTypes;
    }
}
