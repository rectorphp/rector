<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeTypeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Type;
use Rector\Core\Reflection\ReflectionResolver;

final class CallTypeAnalyzer
{
    public function __construct(
        private ReflectionResolver $reflectionResolver
    ) {
    }

    /**
     * @return Type[]
     */
    public function resolveMethodParameterTypes(MethodCall | StaticCall $call): array
    {
        $methodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($call);
        if ($methodReflection === null) {
            return [];
        }

        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());

        $parameterTypes = [];

        /** @var ParameterReflection $parameterReflection */
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            $parameterTypes[] = $parameterReflection->getType();
        }

        return $parameterTypes;
    }
}
