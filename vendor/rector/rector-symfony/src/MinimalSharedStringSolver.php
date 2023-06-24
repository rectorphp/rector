<?php

declare (strict_types=1);
namespace Rector\Symfony;

/**
 * Class Solver copied from https://github.com/Triun/PHP-Longest-Common-Substring
 */
final class MinimalSharedStringSolver
{
    public function solve(string $stringA, string $stringB) : string
    {
        if (\func_num_args() > 2) {
            $arguments = \func_get_args();
            \array_splice($arguments, 0, 2, [$this->solve($stringA, $stringB)]);
            return $this->solve(...$arguments);
        }
        $charsA = \str_split($stringA);
        $charsB = \str_split($stringB);
        $matrix = \array_fill_keys(\array_keys($charsA), \array_fill_keys(\array_keys($charsB), 0));
        $longestLength = 0;
        $longestIndexes = [];
        foreach ($charsA as $i => $charA) {
            foreach ($charsB as $j => $charB) {
                if ($charA === $charB) {
                    $matrix[$i][$j] = $i === 0 || $j === 0 ? 1 : $matrix[$i - 1][$j - 1] + 1;
                    $newIndex = $this->newIndex($matrix, $i, $j);
                    if ($matrix[$i][$j] > $longestLength) {
                        $longestLength = $matrix[$i][$j];
                        $longestIndexes = [$newIndex];
                    } elseif ($matrix[$i][$j] === $longestLength) {
                        $longestIndexes[] = $newIndex;
                    }
                } else {
                    $matrix[$i][$j] = 0;
                }
            }
        }
        return $this->result($longestIndexes, $longestLength, $stringA);
    }
    /**
     * @param array<int, array<int, int>> $matrix
     */
    private function newIndex(array $matrix, int $i, int $j) : int
    {
        return $i - $matrix[$i][$j] + 1;
    }
    /**
     * @param list<int> $longestIndexes
     *
     * @return string the extracted part of string or false on failure.
     */
    private function result(array $longestIndexes, int $longestLength, string $stringA) : string
    {
        return $longestIndexes === [] ? '' : \substr($stringA, $longestIndexes[0], $longestLength);
    }
}
