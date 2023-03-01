<?php

namespace RectorPrefix202303\Triun\LongestCommonSubstring;

/**
 * Interface SolverInterface
 *
 * @package Triun\LongestCommonSubstring
 */
interface SolverInterface
{
    /**
     * @param string $stringA
     * @param string $stringB
     *
     * @return string|mixed
     */
    public function solve(string $stringA, string $stringB);
}
