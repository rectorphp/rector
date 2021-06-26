<?php

declare (strict_types=1);
namespace Rector\Php80\NodeResolver;

use PhpParser\Node\Arg;
use PhpParser\Node\Param;
final class ArgumentSorter
{
    /**
     * @template T as Arg|Param
     * @param array<int, int> $oldToNewPositions
     * @param T[] $argOrParams
     * @return T[]
     */
    public function sortArgsByExpectedParamOrder(array $argOrParams, array $oldToNewPositions) : array
    {
        $newArgsOrParams = [];
        foreach (\array_keys($argOrParams) as $position) {
            $newPosition = $oldToNewPositions[$position] ?? null;
            if ($newPosition === null) {
                continue;
            }
            $newArgsOrParams[$position] = $argOrParams[$newPosition];
        }
        return $newArgsOrParams;
    }
}
