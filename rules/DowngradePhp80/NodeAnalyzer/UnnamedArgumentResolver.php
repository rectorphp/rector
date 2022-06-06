<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp80\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PHPStan\Reflection\FunctionReflection;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\PHPStan\Reflection\Native\NativeFunctionReflection;
use RectorPrefix20220606\PHPStan\Reflection\ParametersAcceptorSelector;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use ReflectionFunction;
final class UnnamedArgumentResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\DowngradePhp80\NodeAnalyzer\NamedToUnnamedArgs
     */
    private $namedToUnnamedArgs;
    public function __construct(NodeNameResolver $nodeNameResolver, NamedToUnnamedArgs $namedToUnnamedArgs)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->namedToUnnamedArgs = $namedToUnnamedArgs;
    }
    /**
     * @param Arg[] $currentArgs
     * @return Arg[]
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection $functionLikeReflection
     */
    public function resolveFromReflection($functionLikeReflection, array $currentArgs) : array
    {
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($functionLikeReflection->getVariants());
        $parameters = $parametersAcceptor->getParameters();
        if ($functionLikeReflection instanceof NativeFunctionReflection) {
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
        $unnamedArgs = $this->namedToUnnamedArgs->fillFromNamedArgs($parameters, $currentArgs, $toFillArgs, $unnamedArgs);
        $unnamedArgs = $this->namedToUnnamedArgs->fillFromJumpedNamedArgs($functionLikeReflection, $unnamedArgs, $parameters);
        \ksort($unnamedArgs);
        return $unnamedArgs;
    }
}
