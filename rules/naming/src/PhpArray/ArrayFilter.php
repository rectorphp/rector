<?php

declare(strict_types=1);

namespace Rector\Naming\PhpArray;

final class ArrayFilter
{
    public function filterWithAtLeastTwoOccurences(array $values): array
    {
        $valueToCount = array_count_values($values);

        $duplicatedValues = [];
        foreach ($valueToCount as $value => $count) {
            if ($count < 2) {
                continue;
            }

            $duplicatedValues[] = $value;
        }

        return $duplicatedValues;
    }
}
