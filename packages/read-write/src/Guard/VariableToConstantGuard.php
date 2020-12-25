<?php

declare(strict_types=1);

namespace Rector\ReadWrite\Guard;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionFunction;

final class VariableToConstantGuard
{
    /**
     * @var array<string, array<int>>
     */
    private $referencePositionsByFunctionName = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isReadArg(Arg $arg): bool
    {
        $parentParent = $arg->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParent instanceof FuncCall) {
            return true;
        }

        $functionName = $this->nodeNameResolver->getName($parentParent);
        if ($functionName === null) {
            return true;
        }

        if (! function_exists($functionName)) {
            // we don't know
            return true;
        }

        $referenceParametersPositions = $this->resolveFunctionReferencePositions($functionName);
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
    private function resolveFunctionReferencePositions(string $functionName): array
    {
        if (isset($this->referencePositionsByFunctionName[$functionName])) {
            return $this->referencePositionsByFunctionName[$functionName];
        }

        $referencePositions = [];

        $reflectionFunction = new ReflectionFunction($functionName);
        foreach ($reflectionFunction->getParameters() as $position => $reflectionParameter) {
            if (! $reflectionParameter->isPassedByReference()) {
                continue;
            }

            $referencePositions[] = $position;
        }

        $this->referencePositionsByFunctionName[$functionName] = $referencePositions;

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
