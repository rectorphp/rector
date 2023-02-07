<?php

namespace Triun\LongestCommonSubstring;

/**
 * Return an array with all longest common strings.
 *
 * Class MatchesSolver
 *
 * @package Triun\LongestCommonSubstring
 */
class MatchesSolver extends Solver
{
    /**
     * @param array $matrix
     * @param int   $i
     * @param int   $j
     *
     * @return Match
     */
    protected function newIndex(array $matrix, int $i, int $j)
    {
        return new Match(
            [
                $i - $matrix[$i][$j] + 1,
                $j - $matrix[$i][$j] + 1,
            ],
            $matrix[$i][$j]
        );
    }

    /**
     * @param Match[] $longestIndexes
     * @param int     $longestLength
     * @param string  $stringA
     * @param string  $stringB
     * @param array   $matrix
     *
     * @return object[] the extracted part of string or false on failure.
     */
    protected function result(
        array $longestIndexes,
        int $longestLength,
        string $stringA,
        string $stringB,
        array $matrix
    ) {
        return array_map(function (Match $result) use ($stringA) {
            $result->value = substr($stringA, $result->index(), $result->length);

            return $result;
        }, $longestIndexes);
    }

    /**
     * @param string $stringA
     * @param string $stringB
     *
     * @return Matches
     */
    public function solve(string $stringA, string $stringB)
    {
        if (func_num_args() > 2) {
            // TODO: Get the best combination, not just the first one.
            $arguments = func_get_args();
            array_splice($arguments, 0, 2, [$this->solve($stringA, $stringB)]);

            return call_user_func_array([$this, 'solve'], $arguments);
        }

        return new Matches(parent::solve($stringA, $stringB));
    }
}
