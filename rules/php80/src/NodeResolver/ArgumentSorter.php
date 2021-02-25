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
        foreach (array_keys($args) as $array_key) {
            $newPosition = $oldToNewPositions[$array_key] ?? null;
            if ($newPosition === null) {
                continue;
            }

            $newArgs[$array_key] = $args[$newPosition];
        }

        return $newArgs;
    }
}
