<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\PHPStan\ScopeFetcher;
use Rector\Reflection\ReflectionResolver;
final class CallLikeArgumentNameAdder
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * Add named arguments to a call-like node starting from the first positional
     * argument whose value satisfies $shouldNameArgValue. All subsequent positional
     * arguments receive names too (required by PHP named-arg semantics).
     *
     * @param callable(Expr):bool $shouldNameArgValue
     */
    public function addNamesToArgs(CallLike $callLike, callable $shouldNameArgValue): ?CallLike
    {
        if ($this->shouldSkip($callLike)) {
            return null;
        }
        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($callLike);
        if (!$reflection instanceof FunctionReflection && !$reflection instanceof MethodReflection) {
            return null;
        }
        $scope = ScopeFetcher::fetch($callLike);
        $args = $callLike->getArgs();
        $parameters = ParametersAcceptorSelectorVariantsWrapper::select($reflection, $callLike, $scope)->getParameters();
        $position = $this->resolveFirstPositionToName($args, $parameters, $shouldNameArgValue);
        if ($position === null) {
            return null;
        }
        $wasChanged = \false;
        $counter = count($args);
        for ($i = $position; $i < $counter; ++$i) {
            $arg = $args[$i];
            if ($arg->name instanceof Identifier) {
                continue;
            }
            $parameterReflection = $this->resolveParameterReflection($arg, $i, $parameters);
            if (!$parameterReflection instanceof ParameterReflection) {
                return null;
            }
            $arg->name = new Identifier($parameterReflection->getName());
            $wasChanged = \true;
        }
        if (!$wasChanged) {
            return null;
        }
        return $callLike;
    }
    private function shouldSkip(CallLike $callLike): bool
    {
        if ($callLike->isFirstClassCallable()) {
            return \true;
        }
        $args = $callLike->getArgs();
        if ($args === []) {
            return \true;
        }
        foreach ($args as $arg) {
            if ($arg->unpack) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Arg[] $args
     * @param ParameterReflection[] $parameters
     * @param callable(Expr):bool $shouldNameArgValue
     */
    private function resolveFirstPositionToName(array $args, array $parameters, callable $shouldNameArgValue): ?int
    {
        foreach ($args as $position => $arg) {
            if ($arg->name instanceof Identifier) {
                continue;
            }
            if (!$shouldNameArgValue($arg->value)) {
                continue;
            }
            if ($this->canNameArgsFromPosition($args, $parameters, $position)) {
                return $position;
            }
        }
        return null;
    }
    /**
     * @param Arg[] $args
     * @param ParameterReflection[] $parameters
     */
    private function canNameArgsFromPosition(array $args, array $parameters, int $position): bool
    {
        $count = count($args);
        for ($i = $position; $i < $count; ++$i) {
            $arg = $args[$i];
            if ($arg->name instanceof Identifier) {
                continue;
            }
            $parameterReflection = $this->resolveParameterReflection($arg, $i, $parameters);
            if (!$parameterReflection instanceof ParameterReflection) {
                return \false;
            }
            if ($parameterReflection->isVariadic()) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param ParameterReflection[] $parameters
     */
    private function resolveParameterReflection(Arg $arg, int $position, array $parameters): ?ParameterReflection
    {
        if ($arg->name instanceof Identifier) {
            foreach ($parameters as $parameter) {
                if ($parameter->getName() === $arg->name->toString()) {
                    return $parameter;
                }
            }
            return null;
        }
        $parameter = $parameters[$position] ?? null;
        if ($parameter instanceof ParameterReflection) {
            return $parameter;
        }
        $lastParameter = end($parameters);
        if ($lastParameter instanceof ParameterReflection && $lastParameter->isVariadic()) {
            return $lastParameter;
        }
        return null;
    }
}
