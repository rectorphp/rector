<?php

declare (strict_types=1);
namespace Rector\ReadWrite\Guard;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class VariableToConstantGuard
{
    /**
     * @var array<string, array<int>>
     */
    private $referencePositionsByFunctionName = [];
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isReadArg(\PhpParser\Node\Arg $arg) : bool
    {
        $parentParent = $arg->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentParent instanceof \PhpParser\Node\Expr\FuncCall) {
            return \true;
        }
        $functionNameString = $this->nodeNameResolver->getName($parentParent);
        if ($functionNameString === null) {
            return \true;
        }
        $functionName = new \PhpParser\Node\Name($functionNameString);
        $argScope = $arg->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$this->reflectionProvider->hasFunction($functionName, $argScope)) {
            // we don't know
            return \true;
        }
        $functionReflection = $this->reflectionProvider->getFunction($functionName, $argScope);
        if (!$argScope instanceof \PHPStan\Analyser\Scope) {
            return \true;
        }
        $referenceParametersPositions = $this->resolveFunctionReferencePositions($functionReflection, [$arg], $argScope);
        if ($referenceParametersPositions === []) {
            // no reference always only write
            return \true;
        }
        $argumentPosition = $this->getArgumentPosition($parentParent, $arg);
        return !\in_array($argumentPosition, $referenceParametersPositions, \true);
    }
    /**
     * @param Arg[] $args
     * @return int[]
     */
    private function resolveFunctionReferencePositions(\PHPStan\Reflection\FunctionReflection $functionReflection, array $args, \PHPStan\Analyser\Scope $scope) : array
    {
        if (isset($this->referencePositionsByFunctionName[$functionReflection->getName()])) {
            return $this->referencePositionsByFunctionName[$functionReflection->getName()];
        }
        $referencePositions = [];
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectFromArgs($scope, $args, $functionReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $position => $parameterReflection) {
            /** @var ParameterReflection $parameterReflection */
            if (!$parameterReflection->passedByReference()->yes()) {
                continue;
            }
            $referencePositions[] = $position;
        }
        $this->referencePositionsByFunctionName[$functionReflection->getName()] = $referencePositions;
        return $referencePositions;
    }
    private function getArgumentPosition(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Arg $desiredArg) : int
    {
        foreach ($funcCall->args as $position => $arg) {
            if ($arg !== $desiredArg) {
                continue;
            }
            return $position;
        }
        throw new \Rector\Core\Exception\ShouldNotHappenException();
    }
}
