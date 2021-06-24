<?php

declare(strict_types=1);

namespace Rector\Php80\NodeResolver;

use PhpParser\Node\Arg;
use PhpParser\Node\Param;
use PHPStan\Reflection\ParameterReflection;

final class ArgumentSorter
{
    /**
     * @template T as Arg|Param
     * @param array<int, ParameterReflection> $expectedOrderedParams
     * @param T[] $args
     * @return T[]
     */
    public function sortArgsByExpectedParamOrder(array $args, array $expectedOrderedParams): array
    {
        $oldToNewPositions = array_keys($expectedOrderedParams);

        $newArgs = [];
        foreach (array_keys($args) as $position) {
            $newPosition = $oldToNewPositions[$position] ?? null;
            if ($newPosition === null) {
                continue;
            }

            $newArgs[$position] = $args[$newPosition];
        }

        return $newArgs;
    }
}
