<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class UnnamedArgumentResolver
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var \Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver
     */
    private $defaultParameterValueResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver $defaultParameterValueResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->defaultParameterValueResolver = $defaultParameterValueResolver;
    }
    /**
     * @param Arg[] $currentArgs
     * @return Arg[]
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection $functionLikeReflection
     */
    public function resolveFromReflection($functionLikeReflection, array $currentArgs) : array
    {
        $parametersAcceptor = $functionLikeReflection->getVariants()[0] ?? null;
        if (!$parametersAcceptor instanceof \PHPStan\Reflection\ParametersAcceptor) {
            return [];
        }
        $unnamedArgs = [];
        foreach ($parametersAcceptor->getParameters() as $paramPosition => $parameterReflection) {
            foreach ($currentArgs as $currentArg) {
                if ($this->shouldSkipParam($currentArg, $parameterReflection)) {
                    continue;
                }
                $unnamedArgs[$paramPosition] = new \PhpParser\Node\Arg($currentArg->value, $currentArg->byRef, $currentArg->unpack, $currentArg->getAttributes(), null);
            }
        }
        $setArgumentPositoins = \array_keys($unnamedArgs);
        $highestParameterPosition = \max($setArgumentPositoins);
        if (!\is_int($highestParameterPosition)) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $unnamedArgs = $this->fillArgValues($highestParameterPosition, $unnamedArgs, $functionLikeReflection);
        \ksort($unnamedArgs);
        return $unnamedArgs;
    }
    private function shouldSkipParam(\PhpParser\Node\Arg $arg, \PHPStan\Reflection\ParameterReflection $parameterReflection) : bool
    {
        if (!$this->nodeNameResolver->isName($arg, $parameterReflection->getName())) {
            return \true;
        }
        return $this->areArgValueAndParameterDefaultValueEqual($parameterReflection, $arg);
    }
    private function areArgValueAndParameterDefaultValueEqual(\PHPStan\Reflection\ParameterReflection $parameterReflection, \PhpParser\Node\Arg $arg) : bool
    {
        // arg value vs parameter default value
        if ($parameterReflection->getDefaultValue() === null) {
            return \false;
        }
        $defaultValue = $this->defaultParameterValueResolver->resolveFromParameterReflection($parameterReflection);
        // default value is set already, let's skip it
        return $this->valueResolver->isValue($arg->value, $defaultValue);
    }
    /**
     * @param Arg[] $unnamedArgs
     * @return Arg[]
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $functionLikeReflection
     */
    private function fillArgValues(int $highestParameterPosition, array $unnamedArgs, $functionLikeReflection) : array
    {
        // fill parameter default values
        for ($i = 0; $i < $highestParameterPosition; ++$i) {
            // the argument is already set, no need to override it
            if (isset($unnamedArgs[$i])) {
                continue;
            }
            $defaultExpr = $this->defaultParameterValueResolver->resolveFromFunctionLikeAndPosition($functionLikeReflection, $i);
            if (!$defaultExpr instanceof \PhpParser\Node\Expr) {
                continue;
            }
            $unnamedArgs[$i] = new \PhpParser\Node\Arg($defaultExpr);
        }
        return $unnamedArgs;
    }
}
