<?php

declare(strict_types=1);

namespace Rector\Php80\NodeResolver;

use PhpParser\Node\Arg;
use PhpParser\Node\Param;

final class ArgumentSorter
{
    /**
     * @param array<int, Param> $expectedOrderedParams
     * @param Arg[] $args
     * @return Arg[]
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
