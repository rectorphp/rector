<?php

declare (strict_types=1);
namespace Rector\CodingStyle\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class SpreadVariablesCollector
{
    /**
     * @return array<int, ParameterReflection>
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $functionLikeReflection
     */
    public function resolveFromMethodReflection($functionLikeReflection) : array
    {
        $spreadParameterReflections = [];
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($functionLikeReflection->getVariants());
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
    public function resolveFromClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $spreadParams = [];
        foreach ($classMethod->params as $key => $param) {
            // prevent race-condition removal on class method
            $originalParam = $param->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE);
            if (!$originalParam instanceof \PhpParser\Node\Param) {
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
