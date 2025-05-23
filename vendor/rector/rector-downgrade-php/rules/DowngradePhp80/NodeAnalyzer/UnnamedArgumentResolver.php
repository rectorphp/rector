<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;
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
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private \Rector\DowngradePhp80\NodeAnalyzer\NamedToUnnamedArgs $namedToUnnamedArgs;
    public function __construct(NodeNameResolver $nodeNameResolver, \Rector\DowngradePhp80\NodeAnalyzer\NamedToUnnamedArgs $namedToUnnamedArgs)
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
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($functionLikeReflection->getVariants());
        $parameters = $extendedParametersAcceptor->getParameters();
        if ($functionLikeReflection instanceof NativeFunctionReflection) {
            $functionLikeReflection = new ReflectionFunction($functionLikeReflection->getName());
        }
        /** @var array<int, Arg> $unnamedArgs */
        $unnamedArgs = [];
        $toFillArgs = [];
        foreach ($currentArgs as $key => $arg) {
            if (!$arg->name instanceof Identifier) {
                $unnamedArgs[$key] = new Arg($arg->value, $arg->byRef, $arg->unpack, [], null);
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
