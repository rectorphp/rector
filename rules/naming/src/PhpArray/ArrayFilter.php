<?php

declare(strict_types=1);

namespace Rector\Naming\PhpArray;

final class ArrayFilter
{
    /**
     * @param mixed[] $values
     * @return string[]
     */
    public function filterWithAtLeastTwoOccurences(array $values): array
    {
        /** @var array<string, int> $valueToCount */
        $valueToCount = array_count_values($values);

        $duplicatedValues = [];

        foreach ($valueToCount as $value => $singleValueToCount) {
            /** @var int $count */
            if ($singleValueToCount < 2) {
                continue;
            }

            /** @var string $value */
            $duplicatedValues[] = $value;
        }

        return $duplicatedValues;
    }
}
