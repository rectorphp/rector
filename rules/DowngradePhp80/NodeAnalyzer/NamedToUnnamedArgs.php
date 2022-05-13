<?php

declare(strict_types=1);

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
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly DefaultParameterValueResolver $defaultParameterValueResolver,
    ) {
    }

    /**
     * @param ParameterReflection[]|PhpParameterReflection[] $parameters
     * @param array<int, Arg> $currentArgs
     * @param string[] $toFillArgs
     * @param array<int, Arg> $unnamedArgs
     * @return array<int, Arg>
     */
    public function fillFromNamedArgs(
        array $parameters,
        array $currentArgs,
        array $toFillArgs,
        array $unnamedArgs
    ): array {
        foreach ($parameters as $paramPosition => $parameterReflection) {
            $parameterReflectionName = $parameterReflection->getName();
            if (! in_array($parameterReflectionName, $toFillArgs, true)) {
                continue;
            }

            foreach ($currentArgs as $currentArg) {
                if (! $currentArg->name instanceof Identifier) {
                    continue;
                }

                if (! $this->nodeNameResolver->isName($currentArg->name, $parameterReflectionName)) {
                    continue;
                }

                $unnamedArgs[$paramPosition] = new Arg(
                    $currentArg->value,
                    $currentArg->byRef,
                    $currentArg->unpack,
                    $currentArg->getAttributes(),
                    null
                );
            }
        }

        return $unnamedArgs;
    }

    /**
     * @param array<int, Arg> $unnamedArgs
     * @param ParameterReflection[]|PhpParameterReflection[] $parameters
     * @return array<int, Arg>
     */
    public function fillFromJumpedNamedArgs(
        FunctionReflection | MethodReflection | ReflectionFunction $functionLikeReflection,
        array $unnamedArgs,
        array $parameters
    ): array {
        $keys = array_keys($unnamedArgs);
        if ($keys === []) {
            return $unnamedArgs;
        }

        $highestParameterPosition = max($keys);
        $parametersCount = count($parameters);
        for ($i = 0; $i < $parametersCount; ++$i) {
            if (in_array($i, $keys, true)) {
                continue;
            }

            if ($i > $highestParameterPosition) {
                continue;
            }

            /** @var ParameterReflection|PhpParameterReflection $parameterReflection */
            if ($functionLikeReflection instanceof ReflectionFunction) {
                // @todo since PHPStan 1.7.* add new InitializerExprTypeResolver() service as 1st arg - https://github.com/phpstan/phpstan-src/commit/c8b3926f005d008178d6d8c62aaca0200a6359a2#diff-ce65c81a2653b1f53bc416082582e248f629d65c066440d9c4edc5005d16af32
                $parameterReflection = new PhpParameterReflection(
                    $functionLikeReflection->getParameters()[$i],
                    null,
                    null
                );
            } else {
                $parameterReflection = $parameters[$i];
            }

            $defaultValue = $this->defaultParameterValueResolver->resolveFromParameterReflection($parameterReflection);
            if (! $defaultValue instanceof Expr) {
                continue;
            }

            $unnamedArgs[$i] = new Arg(
                $defaultValue,
                $parameterReflection->passedByReference()
                    ->yes(),
                $parameterReflection->isVariadic(),
                [],
                null
            );
        }

        return $unnamedArgs;
    }
}
