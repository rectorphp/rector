<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\Php\PhpParameterReflection;
use Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use ReflectionFunction;
final class NamedToUnnamedArgs
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver
     */
    private $defaultParameterValueResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver $defaultParameterValueResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->defaultParameterValueResolver = $defaultParameterValueResolver;
    }
    /**
     * @param ParameterReflection[]|PhpParameterReflection[] $parameters
     * @param Arg[] $currentArgs
     * @param string[] $toFillArgs
     * @param Arg[] $unnamedArgs
     * @return Arg[]
     */
    public function fillFromNamedArgs(array $parameters, array $currentArgs, array $toFillArgs, array $unnamedArgs) : array
    {
        foreach ($parameters as $paramPosition => $parameterReflection) {
            $parameterReflectionName = $parameterReflection->getName();
            if (!\in_array($parameterReflectionName, $toFillArgs, \true)) {
                continue;
            }
            foreach ($currentArgs as $currentArg) {
                if (!$currentArg->name instanceof \PhpParser\Node\Identifier) {
                    continue;
                }
                if (!$this->nodeNameResolver->isName($currentArg->name, $parameterReflectionName)) {
                    continue;
                }
                $unnamedArgs[$paramPosition] = new \PhpParser\Node\Arg($currentArg->value, $currentArg->byRef, $currentArg->unpack, $currentArg->getAttributes(), null);
            }
        }
        return $unnamedArgs;
    }
    /**
     * @param Arg[] $unnamedArgs
     * @param ParameterReflection[]|PhpParameterReflection[] $parameters
     * @return Arg[]
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection|\ReflectionFunction $functionLikeReflection
     */
    public function fillFromJumpedNamedArgs($functionLikeReflection, array $unnamedArgs, bool $isNativeFunctionReflection, array $parameters) : array
    {
        $keys = \array_keys($unnamedArgs);
        if ($keys === []) {
            return $unnamedArgs;
        }
        $highestParameterPosition = \max($keys);
        $parametersCount = \count($parameters);
        for ($i = 0; $i < $parametersCount; ++$i) {
            if (\in_array($i, $keys, \true)) {
                continue;
            }
            if ($i > $highestParameterPosition) {
                continue;
            }
            /** @var ParameterReflection|PhpParameterReflection $parameterReflection */
            if ($isNativeFunctionReflection) {
                /** @var ReflectionFunction $functionLikeReflection */
                $parameterReflection = new \PHPStan\Reflection\Php\PhpParameterReflection($functionLikeReflection->getParameters()[$i], null, null);
            } else {
                $parameterReflection = $parameters[$i];
            }
            $defaulValue = $this->defaultParameterValueResolver->resolveFromParameterReflection($parameterReflection);
            if (!$defaulValue instanceof \PhpParser\Node\Expr) {
                continue;
            }
            $unnamedArgs[$i] = new \PhpParser\Node\Arg($defaulValue, $parameterReflection->passedByReference()->yes(), $parameterReflection->isVariadic(), [], null);
        }
        return $unnamedArgs;
    }
}
