<?php

declare(strict_types=1);

namespace Rector\Naming\PhpArray;

final class ArrayFilter
{
    /**
     * @return int[]|string[]
     */
    public function filterWithAtLeastTwoOccurences(array $values): array
    {
        $valueToCount = array_count_values($values);

        $duplicatedValues = [];
        foreach ($valueToCount as $value => $count) {
            if ($count < 2) {
                continue;
            }

            /** @var string $value */
            $duplicatedValues[] = $value;
        }

        return $duplicatedValues;
    }
}
