<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Utils;

use RectorPrefix202608\Entropy\Attributes\RelatedTest;
use RectorPrefix202608\Entropy\Tests\Utils\FuzzyMatcherTest;
final class FuzzyMatcher
{
    /**
     * @param string[] $candidates
     */
    public static function match(string $input, array $candidates): ?string
    {
        if ($input === '' || $candidates === []) {
            return null;
        }
        // 0. exact match (always safe)
        if (in_array($input, $candidates, \true)) {
            return $input;
        }
        // 1. handle single-letter input STRICTLY
        if (strlen($input) === 1) {
            $prefixMatches = array_values(array_filter($candidates, static fn(string $candidate): bool => strncmp($candidate, $input, strlen($input)) === 0));
            return count($prefixMatches) === 1 ? $prefixMatches[0] : null;
        }
        // 2. prefix match (multi-letter)
        $prefixMatches = array_values(array_filter($candidates, static fn(string $candidate): bool => strncmp($candidate, $input, strlen($input)) === 0));
        if (count($prefixMatches) === 1) {
            return $prefixMatches[0];
        }
        // 3. levenshtein typo match (+ allow one adjacent swap)
        $distances = [];
        foreach ($candidates as $candidate) {
            $distance = levenshtein($input, $candidate);
            // Treat "tset" vs "test" (single adjacent transposition) as 1 edit
            if ($distance === 2 && self::isSingleAdjacentSwap($input, $candidate)) {
                $distance = 1;
            }
            $distances[$candidate] = $distance;
        }
        asort($distances);
        $best = array_key_first($distances);
        $bestDistance = $distances[$best];
        // conservative threshold
        $maxAllowed = max(1, (int) floor(strlen($best) / 3));
        return $bestDistance <= $maxAllowed ? $best : null;
    }
    /**
     * Returns true if $a can become $b by swapping exactly one adjacent pair.
     */
    private static function isSingleAdjacentSwap(string $a, string $b): bool
    {
        if (strlen($a) !== strlen($b) || strlen($a) < 2) {
            return \false;
        }
        $len = strlen($a);
        // find first mismatch
        $i = 0;
        while ($i < $len && $a[$i] === $b[$i]) {
            ++$i;
        }
        // no mismatch
        if ($i >= $len - 1) {
            return \false;
        }
        // must be a swap at i and i+1
        if ($a[$i] !== $b[$i + 1] || $a[$i + 1] !== $b[$i]) {
            return \false;
        }
        // rest must match
        for ($j = $i + 2; $j < $len; ++$j) {
            if ($a[$j] !== $b[$j]) {
                return \false;
            }
        }
        return \true;
    }
}
