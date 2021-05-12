<?php

declare(strict_types=1);

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
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class VariableToConstantGuard
{
    /**
     * @var array<string, array<int>>
     */
    private array $referencePositionsByFunctionName = [];

    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private ReflectionProvider $reflectionProvider,
        private PrivatesAccessor $privatesAccessor
    ) {
    }

    public function isReadArg(Arg $arg): bool
    {
        $parentParent = $arg->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParent instanceof FuncCall) {
            return true;
        }

        $functionNameString = $this->nodeNameResolver->getName($parentParent);
        if ($functionNameString === null) {
            return true;
        }

        $functionName = new Name($functionNameString);
        $argScope = $arg->getAttribute(AttributeKey::SCOPE);

        if (! $this->reflectionProvider->hasFunction($functionName, $argScope)) {
            // we don't know
            return true;
        }

        $functionReflection = $this->reflectionProvider->getFunction($functionName, $argScope);

        $referenceParametersPositions = $this->resolveFunctionReferencePositions($functionReflection);
        if ($referenceParametersPositions === []) {
            // no reference always only write
            return true;
        }

        $argumentPosition = $this->getArgumentPosition($parentParent, $arg);
        return ! in_array($argumentPosition, $referenceParametersPositions, true);
    }

    /**
     * @return int[]
     */
    private function resolveFunctionReferencePositions(FunctionReflection $functionReflection): array
    {
        if (isset($this->referencePositionsByFunctionName[$functionReflection->getName()])) {
            return $this->referencePositionsByFunctionName[$functionReflection->getName()];
        }

        // this is needed, as native function reflection does not have access to referenced parameters
        if ($functionReflection instanceof NativeFunctionReflection) {
            $nativeFunctionReflection = new ReflectionFunction($functionReflection->getName());
        } else {
            $nativeFunctionReflection = $this->privatesAccessor->getPrivateProperty($functionReflection, 'reflection');
        }

        $referencePositions = [];
        /** @var int $position */
        foreach ($nativeFunctionReflection->getParameters() as $position => $reflectionParameter) {
            if (! $reflectionParameter->isPassedByReference()) {
                continue;
            }

            $referencePositions[] = $position;
        }

        $this->referencePositionsByFunctionName[$functionReflection->getName()] = $referencePositions;

        return $referencePositions;
    }

    private function getArgumentPosition(FuncCall $funcCall, Arg $desiredArg): int
    {
        foreach ($funcCall->args as $position => $arg) {
            if ($arg !== $desiredArg) {
                continue;
            }

            return $position;
        }

        throw new ShouldNotHappenException();
    }
}
