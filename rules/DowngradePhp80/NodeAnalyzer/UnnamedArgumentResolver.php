<?php

declare (strict_types=1);
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
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\DowngradePhp80\NodeAnalyzer\NamedToUnnamedArgs $namedToUnnamedArgs)
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
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($functionLikeReflection->getVariants());
        $parameters = $parametersAcceptor->getParameters();
        if ($functionLikeReflection instanceof \PHPStan\Reflection\Native\NativeFunctionReflection) {
            $functionLikeReflection = new \ReflectionFunction($functionLikeReflection->getName());
        }
        /** @var Arg[] $unnamedArgs */
        $unnamedArgs = [];
        $toFillArgs = [];
        foreach ($currentArgs as $key => $arg) {
            if ($arg->name === null) {
                $unnamedArgs[$key] = new \PhpParser\Node\Arg($arg->value, $arg->byRef, $arg->unpack, $arg->getAttributes(), null);
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
