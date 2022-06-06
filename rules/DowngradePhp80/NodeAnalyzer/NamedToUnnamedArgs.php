<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp80\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PHPStan\Reflection\FunctionReflection;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\PHPStan\Reflection\ParameterReflection;
use RectorPrefix20220606\PHPStan\Reflection\Php\PhpParameterReflection;
use RectorPrefix20220606\Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver;
use RectorPrefix20220606\Rector\DowngradePhp80\Reflection\SimplePhpParameterReflection;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use ReflectionFunction;
final class NamedToUnnamedArgs
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver
     */
    private $defaultParameterValueResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, DefaultParameterValueResolver $defaultParameterValueResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->defaultParameterValueResolver = $defaultParameterValueResolver;
    }
    /**
     * @param ParameterReflection[]|PhpParameterReflection[] $parameters
     * @param array<int, Arg> $currentArgs
     * @param string[] $toFillArgs
     * @param array<int, Arg> $unnamedArgs
     * @return array<int, Arg>
     */
    public function fillFromNamedArgs(array $parameters, array $currentArgs, array $toFillArgs, array $unnamedArgs) : array
    {
        foreach ($parameters as $paramPosition => $parameterReflection) {
            $parameterReflectionName = $parameterReflection->getName();
            if (!\in_array($parameterReflectionName, $toFillArgs, \true)) {
                continue;
            }
            foreach ($currentArgs as $currentArg) {
                if (!$currentArg->name instanceof Identifier) {
                    continue;
                }
                if (!$this->nodeNameResolver->isName($currentArg->name, $parameterReflectionName)) {
                    continue;
                }
                $unnamedArgs[$paramPosition] = new Arg($currentArg->value, $currentArg->byRef, $currentArg->unpack, $currentArg->getAttributes(), null);
            }
        }
        return $unnamedArgs;
    }
    /**
     * @param array<int, Arg> $unnamedArgs
     * @param ParameterReflection[]|PhpParameterReflection[] $parameters
     * @return array<int, Arg>
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection|\ReflectionFunction $functionLikeReflection
     */
    public function fillFromJumpedNamedArgs($functionLikeReflection, array $unnamedArgs, array $parameters) : array
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
            if ($functionLikeReflection instanceof ReflectionFunction) {
                $parameterReflection = new SimplePhpParameterReflection($functionLikeReflection, $i);
            } else {
                $parameterReflection = $parameters[$i];
            }
            $defaultValue = $this->defaultParameterValueResolver->resolveFromParameterReflection($parameterReflection);
            if (!$defaultValue instanceof Expr) {
                continue;
            }
            $unnamedArgs[$i] = new Arg($defaultValue, $parameterReflection->passedByReference()->yes(), $parameterReflection->isVariadic(), [], null);
        }
        return $unnamedArgs;
    }
}
