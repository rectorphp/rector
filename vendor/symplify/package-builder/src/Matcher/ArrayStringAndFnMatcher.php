<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\PackageBuilder\Matcher;

/**
 * @api
 */
final class ArrayStringAndFnMatcher
{
    /**
     * @param string[] $matchingValues
     */
    public function isMatchWithIsA(string $currentValue, array $matchingValues) : bool
    {
        if ($this->isMatch($currentValue, $matchingValues)) {
            return \true;
        }
        foreach ($matchingValues as $matchingValue) {
            if (\is_a($currentValue, $matchingValue, \true)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param string[] $matchingValues
     */
    public function isMatch(string $currentValue, array $matchingValues) : bool
    {
        foreach ($matchingValues as $matchingValue) {
            if ($currentValue === $matchingValue) {
                return \true;
            }
            if (\fnmatch($matchingValue, $currentValue)) {
                return \true;
            }
            if (\fnmatch($matchingValue, $currentValue, \FNM_NOESCAPE)) {
                return \true;
            }
        }
        return \false;
    }
}
