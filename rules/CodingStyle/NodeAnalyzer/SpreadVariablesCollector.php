<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Reflection\FunctionReflection;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\PHPStan\Reflection\ParameterReflection;
use RectorPrefix20220606\PHPStan\Reflection\ParametersAcceptorSelector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class SpreadVariablesCollector
{
    /**
     * @return array<int, ParameterReflection>
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $functionLikeReflection
     */
    public function resolveFromMethodReflection($functionLikeReflection) : array
    {
        $spreadParameterReflections = [];
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($functionLikeReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $key => $parameterReflection) {
            if (!$parameterReflection->isVariadic()) {
                continue;
            }
            $spreadParameterReflections[$key] = $parameterReflection;
        }
        return $spreadParameterReflections;
    }
    /**
     * @return array<int, Param>
     */
    public function resolveFromClassMethod(ClassMethod $classMethod) : array
    {
        /** @var array<int, Param> $spreadParams */
        $spreadParams = [];
        foreach ($classMethod->params as $key => $param) {
            // prevent race-condition removal on class method
            $originalParam = $param->getAttribute(AttributeKey::ORIGINAL_NODE);
            if (!$originalParam instanceof Param) {
                continue;
            }
            if (!$originalParam->variadic) {
                continue;
            }
            $spreadParams[$key] = $param;
        }
        return $spreadParams;
    }
}
