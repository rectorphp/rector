<?php

declare (strict_types=1);
namespace Rector\ReadWrite\Guard;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionFunction;
use RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
final class VariableToConstantGuard
{
    /**
     * @var array<string, array<int>>
     */
    private $referencePositionsByFunctionName = [];
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->privatesAccessor = $privatesAccessor;
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
        $referenceParametersPositions = $this->resolveFunctionReferencePositions($functionReflection);
        if ($referenceParametersPositions === []) {
            // no reference always only write
            return \true;
        }
        $argumentPosition = $this->getArgumentPosition($parentParent, $arg);
        return !\in_array($argumentPosition, $referenceParametersPositions, \true);
    }
    /**
     * @return int[]
     */
    private function resolveFunctionReferencePositions(\PHPStan\Reflection\FunctionReflection $functionReflection) : array
    {
        if (isset($this->referencePositionsByFunctionName[$functionReflection->getName()])) {
            return $this->referencePositionsByFunctionName[$functionReflection->getName()];
        }
        // this is needed, as native function reflection does not have access to referenced parameters
        if ($functionReflection instanceof \PHPStan\Reflection\Native\NativeFunctionReflection) {
            $functionName = $functionReflection->getName();
            if (!\function_exists($functionName)) {
                return [];
            }
            $nativeFunctionReflection = new \ReflectionFunction($functionName);
        } else {
            $nativeFunctionReflection = $this->privatesAccessor->getPrivateProperty($functionReflection, 'reflection');
        }
        $referencePositions = [];
        /** @var int $position */
        foreach ($nativeFunctionReflection->getParameters() as $position => $reflectionParameter) {
            if (!$reflectionParameter->isPassedByReference()) {
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
