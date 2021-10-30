<?php

declare (strict_types=1);
namespace Rector\Php80\NodeResolver;

use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
final class RequireOptionalParamResolver
{
    /**
     * @return ParameterReflection[]
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection $functionLikeReflection
     */
    public function resolveFromReflection($functionLikeReflection) : array
    {
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($functionLikeReflection->getVariants());
        $optionalParams = [];
        $requireParams = [];
        foreach ($parametersAcceptor->getParameters() as $position => $parameterReflection) {
            if ($parameterReflection->getDefaultValue() === null && !$parameterReflection->isVariadic()) {
                $requireParams[$position] = $parameterReflection;
            } else {
                $optionalParams[$position] = $parameterReflection;
            }
        }
        return $requireParams + $optionalParams;
    }
}
