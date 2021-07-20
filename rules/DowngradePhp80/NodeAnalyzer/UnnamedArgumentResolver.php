<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\NodeAnalyzer;

use PhpParser\Node\Arg;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\NodeNameResolver\NodeNameResolver;
use ReflectionFunction;

final class UnnamedArgumentResolver
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private NamedToUnnamedArgs $namedToUnnamedArgs
    ) {
    }

    /**
     * @param Arg[] $currentArgs
     * @return Arg[]
     */
    public function resolveFromReflection(
        FunctionReflection | MethodReflection $functionLikeReflection,
        array $currentArgs
    ): array {
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($functionLikeReflection->getVariants());
        $unnamedArgs = [];
        $parameters = $parametersAcceptor->getParameters();
        $isNativeFunctionReflection = $functionLikeReflection instanceof NativeFunctionReflection;

        if ($isNativeFunctionReflection) {
            $functionLikeReflection = new ReflectionFunction($functionLikeReflection->getName());
        }

        /** @var Arg[] $unnamedArgs */
        $unnamedArgs = [];
        $toFillArgs = [];
        foreach ($currentArgs as $key => $arg) {
            if ($arg->name === null) {
                $unnamedArgs[$key] = new Arg($arg->value, $arg->byRef, $arg->unpack, $arg->getAttributes(), null);

                continue;
            }

            /** @var string $argName */
            $argName = $this->nodeNameResolver->getName($arg->name);
            $toFillArgs[] = $argName;
        }

        $unnamedArgs = $this->namedToUnnamedArgs->fillFromNamedArgs(
            $parameters,
            $currentArgs,
            $toFillArgs,
            $unnamedArgs
        );
        $unnamedArgs = $this->namedToUnnamedArgs->fillFromJumpedNamedArgs(
            $functionLikeReflection,
            $unnamedArgs,
            $isNativeFunctionReflection,
            $parameters
        );
        ksort($unnamedArgs);
        return $unnamedArgs;
    }
}
